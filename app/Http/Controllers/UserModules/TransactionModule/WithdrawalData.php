<?php

namespace App\Http\Controllers\UserModules\TransactionModule;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Deposit;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Notifications\AdminMail;
use App\Notifications\UserNotification;
use App\Notifications\WithdrawalMailLater;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WithdrawalData extends BaseController
{
    use PubFunctions;
    //get withdrawal recipient data by phone
    public function getWithdrawalRecipientDetailByPhone($phone)
    {
        $user = Auth::user();
        $web = GeneralSetting::find(1);

        $recipient = User::where('phone',$phone)->first();

        if (empty($recipient)){
            return $this->sendError('validation.error',['error'=>'User not found.'],422);
        }

        $dataResponse=[
            'id'=>$recipient->id,
            'ref'=>$recipient->userRef,
            'email'=>$recipient->email
        ];

        return $this->sendResponse($dataResponse,'retrieved');

    }
    //get withdrawal recipient data by Email
    public function getWithdrawalRecipientDetailByEmail($email)
    {
        $user = Auth::user();
        $web = GeneralSetting::find(1);

        $recipient = User::where('email',$email)->first();

        if (empty($recipient)){
            return $this->sendError('validation.error',['error'=>'User not found.'],422);
        }

        $dataResponse=[
            'id'=>$recipient->id,
            'ref'=>$recipient->userRef,
            'email'=>$recipient->email
        ];

        return $this->sendResponse($dataResponse,'retrieved');

    }
    public function sendCryptoUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $web = GeneralSetting::find(1);
        $validator = Validator::make($request->all(),[
            'recipient'=>['required','string'],
            'asset'=>['required','alpha_dash'],
            'amount'=>['required','numeric'],
            'password'=>['required','current_password:api'],
            'code'=>['required','numeric'],
            'purpose'=>['required','string']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }

        $input = $validator->validated();

        $stat = $this->verifyOtpSent($user,$input['code'],$input['purpose']);
        if (!$stat['status']){
            return $this->sendError('otp.error',['error'=>$stat['error']]);
        }


        $coin = Coin::where('asset',$input['asset'])->first();

        //check if recipient exists
        $recipient = User::where(['userRef'=>$input['recipient'],'status'=>1])->first();
        if (empty($recipient)){
            return $this->sendError('validation.error',['error'=>'Invalid recipient or recipient account not
            active'],422);
        }
        //check of the wallet exists
        $wallet = UserWallet::where(['user'=>$user->id,'asset'=>strtoupper($input['asset'])])->first();
        if (empty($wallet)){
            return $this->sendError('validation.error',['error'=>'Unsupported asset'],422);
        }
        //get recipient's wallet
        $walletTo = UserWallet::where(['user'=>$recipient->id,'asset'=>$input['asset']])->first();

        //check if the wallet has enough balance
        if ($input['amount'] > $wallet->floatBalance) {
            return $this->sendError('balance.error', ['error' =>
                'Insufficient Balance for transfer of '.$input['amount']." ".$input['asset']
            ],
                422);
        }

        if ($coin->minSend > $input['amount']){
            return $this->sendError('transfer.error', [
                'error' => 'You can only send a minimum of '.$coin->minSend.' '.$coin->asset],
                422);
        }
        $rate = $this->getRateInstant($input['asset'],'USD');
        //process the sending
        return $this->processWithdrawalToUser($user,$recipient,$input,$walletTo,$wallet,$rate,$request);
    }
    private  function processWithdrawalToUser($sender,$recipient,$input,$walletTo,$walletFrom,$rate, Request $request)
    {
        $coin = Coin::where('asset',$input['asset'])->first();
        $balanceDetail=[
            'floatBalance'=>$walletFrom->floatBalance - ($input['amount'])
        ];

        $ref = $this->generateRef('withdrawals','reference');
        $dataWithdrawal = [
            'user'=>$sender->id,'asset'=>$walletFrom->asset,'amount'=>$input['amount'],
            'fiatAmount'=>$input['amount']*$rate,'accountId'=>$walletFrom->accountId,
            'fee'=>$coin->networkFee,'withdrawalType'=>2,'destination'=>$input['recipient'],
            'addressTo'=>$walletTo->address,'reference'=>$ref,
            'memo'=>$walletTo->memo,'hasMemo'=>$walletTo->hasMemo,'isSystem'=>2,'status'=>1,
            'balance'=>$walletFrom->availableBalance-$input['amount'],'recipient'=>$recipient->id
        ];

        $dataDeposit =[
            'user'=>$recipient->id,'amount'=>$input['amount'],
            'asset'=>$walletFrom->asset,'addressFrom'=>$walletFrom->address,
            'addressTo'=>$walletTo->address,'memo'=>$walletTo->memo,
            'hasMemo'=>$walletTo->hasMemo,'accountId'=>$walletTo->accountId,
            'transHash'=>$ref
        ];

        $dataReceiverWallet=[
            'floatBalance'=>$walletTo->floatBalance+$input['amount']
        ];

        $withdrawal = Withdrawal::create($dataWithdrawal);
        if (!empty($withdrawal)){
            Deposit::create($dataDeposit);

            UserWallet::where(['user'=>$sender->id,'id'=>$walletFrom->id])->update($balanceDetail);

            UserWallet::where('id',$walletTo->id)->update($dataReceiverWallet);

            $message= sprintf("
                <b>Transfer Reference</b> %s<br><br>
                <b>Date</b> %s<br><br>
                <b>Amount</b> %s%s<br><br>
                <b>Recipient</b> %s<br><br>
            ", $ref, date('d-m-Y h:i a', time()), $input['amount'], $input['asset'], $input['recipient']);
            $sender->notify(new WithdrawalMailLater($sender,$message));
            //send app notification to sender
            $senderAppMessage =number_format($input['amount'],6)." ".$coin->name." sent to ".$recipient->name;
            $sender->notify(new UserNotification($sender,$senderAppMessage,'New transfer to user'));

            //let's send mail to admin to notify him about this
            $admin = User::where('isAdmin',1)->first();
            if(!empty($admin)){
                $message = "
                    A new Withdrawal of ".$coin->name." has been placed on <b>".env('APP_NAME')."</b>.
                    Find Transaction details below:<br><br>
                    <b>Transaction Reference</b>:".$ref."<br><br>
                    <b>Amount</b>: ".$input['amount']." ".$input['asset']."<br><br>
                    <b>Recipient </b>:".$recipient->name." (".$recipient->email.")<br><br>
                    <b>Type</b>: Internal Transfer To user<br><br>
                ";

                $admin->notify(new AdminMail($admin,$message,'New '.$coin->name.' Withdrawal'));
            }
            //send notification to receiver to notify them of this incoming
            $messageToReceiver = "
                   You have received some ".$coin->name." on your <b>".env('APP_NAME')."</b> account.
                    Find Transaction details below:<br><br>
                    <b>Transaction Reference</b>:".$ref."<br><br>
                    <b>Amount</b>: ".$input['amount']." ".$input['asset']."<br>
                    <b>Sender </b>:".$sender->name." (".$sender->email.")<br><br>
                    <b>Type</b>: Transfer To user<br><br>
            ";
            $recipient->notify(new AdminMail($recipient,$messageToReceiver,'New '.$coin->name.' deposit.'));
            //send app notification to received
            $appMessage = number_format($input['amount'],6)." ".$coin->name." deposit
            received from ".$sender->name;
            $recipient->notify(new UserNotification($recipient,$appMessage,'New deposit received'));

            $dataResponse=[
                'token'=>$request->bearerToken(),
                'name'=>$sender->name,
                'amount'=>$input['amount'],
                'asset'=>$input['asset'],
                'reference'=>$ref
            ];
            return $this->sendResponse($dataResponse,'transfer successful.');
        }
        return $this->sendError('transfer.error',['error'=>'Something went wrong']);
    }
    public function sendCryptoToExternal(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $web = GeneralSetting::find(1);
        $validator = Validator::make($request->all(),[
            'address'=>['required','string'],
            'asset'=>['required','alpha_dash'],
            'amount'=>['required','numeric'],
            'password'=>['required','current_password:api'],
            'memo'=>['nullable','string'],
            'purpose'=>['required','string'],
            'code'=>['required','numeric']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }

        $input = $validator->validated();

        //check otp
        $stat = $this->verifyOtpSent($user,$input['code'],$input['purpose']);
        if (!$stat['status']){
            return $this->sendError('otp.error',['error'=>$stat['error']]);
        }

        $coin = Coin::where('asset',$input['asset'])->first();

        //check of the wallet exists
        $wallet = UserWallet::where(['user'=>$user->id,'asset'=>strtoupper($input['asset'])])->first();
        if (empty($wallet)){
            return $this->sendError('validation.error',['error'=>'Unsupported asset'],422);
        }
        //check if the wallet has enough balance
        if (($input['amount']+$coin->networkFee)>$wallet->floatBalance) {
            return $this->sendError('balance.error',
                ['error' => 'Insufficient Balance for transfer of '.$input['amount']],
                422
            );
        }
        if ($coin->minSend > $input['amount']){
            return $this->sendError('transfer.error', [
                'error' => 'You can only send a minimum of '.$coin->minSend.' '.$coin->asset],
                422);
        }

        $rate = $this->getRateInstant($input['asset'],'USD');
        //process the sending
        return $this->processWithdrawalToExternal($user,$input,$wallet,$rate,$request);
    }

    private  function processWithdrawalToExternal($sender,$input,$walletFrom,$rate,$request)
    {
        $coin = Coin::where('asset',$input['asset'])->first();
        $balanceDetail=[
            'floatBalance'=>$walletFrom->floatBalance - ($input['amount']+$coin->networkFee)
        ];
        $hasMemo = (empty($input['memo']))?2:1;
        $ref = $this->generateRef('withdrawals','reference');
        $dataWithdrawal = [
            'user'=>$sender->id,'asset'=>$walletFrom->asset,'amount'=>$input['amount'],
            'fiatAmount'=>$input['amount']*$rate,'accountId'=>$walletFrom->accountId,
            'fee'=>$coin->networkFee,'withdrawalType'=>2,'destination'=>'external',
            'addressTo'=>$input['address'],'reference'=>$ref,
            'memo'=>$input['memo'],'hasMemo'=>$hasMemo,'isSystem'=>2,'status'=>2,
            'balance'=>$walletFrom->floatBalance-($input['amount']+$coin->networkFee),
        ];

        $withdrawal = Withdrawal::create($dataWithdrawal);
        if (!empty($withdrawal)){
            //Deposit::create($dataDeposit);

            UserWallet::where(['user'=>$sender->id,'id'=>$walletFrom->id])->update($balanceDetail);

            //Wallet::where('id',$walletTo->id)->update($dataReceiverWallet);

            $message= sprintf("
                <b>Withdrawal Reference</b> %s<br><br>
                <b>Date</b> %s<br><br>
                <b>Amount</b> %s%s<br><br>
                <b>Recipient</b> %s<br><br>
            ", $ref, date('d-m-Y h:i a', time()), $input['amount'], $input['asset'], $input['address']);

            $sender->notify(new WithdrawalMailLater($sender,$message));

            //send app notification
            $appMessage =number_format($input['amount'],6)." ".$coin->name." withdrawn from account";
            $sender->notify(new UserNotification($sender,$appMessage,'New deposit'));

            //let's send mail to admin to notify him about this
            $admin = User::where('isAdmin',1)->first();
            if(!empty($admin)){
                $message = "
                    A new Withdrawal of ".$coin->name." has been placed on <b>".env('APP_NAME')."</b>.
                    Find Transaction details below:<br><br>
                    <b>Transaction Reference</b>:".$ref."<br><br>
                    <b>Amount</b>: ".$input['amount']." ".$input['asset']."<br><br>
                    <b>Recipient </b>:".$input['address']."<br><br>
                    <b>Memo</b>:".$input['memo']."<br><br>
                    <b>Type</b>: External Withdrawal<br><br>
                ";

                $admin->notify(new AdminMail($admin,$message,'New '.$coin->name.' Withdrawal'));
            }
            $dataResponse=[
                'token'=>$request->bearerToken(),
                'name'=>$sender->name,
                'amount'=>$input['amount'],
                'asset'=>$input['asset'],
                'reference'=>$ref
            ];
            return $this->sendResponse($dataResponse,'transfer successful.');
        }
        return $this->sendError('transfer.error',['error'=>'Something went wrong']);
    }

}
