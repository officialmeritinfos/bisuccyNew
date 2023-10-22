<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Coin;
use App\Models\Deposit;
use App\Models\FiatWithdrawal;
use App\Models\GeneralSetting;
use App\Models\Notification;
use App\Models\Purchase;
use App\Models\ReferralEarning;
use App\Models\Sale;
use App\Models\SignalEnrollmentPayment;
use App\Models\SignalPackage;
use App\Models\Swap;
use App\Models\User;
use App\Models\UserBank;
use App\Models\UserVerification;
use App\Models\Withdrawal;
use App\Notifications\AdminMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Users extends BaseController
{
    public function landingPage()
    {
        return view('users.index');
    }

    public function getUsers($index = 0)
    {
        $users = User::where('isAdmin','!=',1)->offset($index*50)->limit(50)->get();
        if ($users->count()<1){
            return $this->sendError('account.error',['error'=>'No data found']);
        }

        $dataCo=[];
        foreach ($users as $user) {
            $package = SignalPackage::where('id',$user->packageEnrolled)->first();
            $refEarnings = ReferralEarning::where('referrer',$user->id)->get();
            $datRefEarn = [];

            foreach ($refEarnings as $refEarning) {
                $dataRefEarn = [
                    'amount'=>$refEarning->amount,
                    'timeEarned'=>strtotime($refEarning->created_at)
                ];

                $datRefEarn[]=$dataRefEarn;
            }

            $data = [
                'id'=>$user->id,
                'reference'=>$user->userRef,
                'email'=>$user->email,
                'registrationIp'=>$user->regIp,
                'country'=>$user->country,
                'phoneCode'=>$user->phoneCode,
                'phone'=>$user->phone,
                'state'=>$user->state,
                'city'=>$user->city,
                'address'=>$user->address,
                'accountCurrency'=>$user->mainCurrency,
                'twoFactor'=>($user->twoFactor==1)?'on':'off',
                'emailVerified'=>($user->emailVerified==1)?'yes':'no',
                'phoneVerified'=>($user->phoneVerified==1)?'yes':'no',
                'accountVerified'=>($user->accountVerified==1)?'verified':'unverified',
                'photo'=>asset('user/photo/'.$user->photo),
                'canSendCrypto'=>($user->canSend==1)?'on':'off',
                'canDeposit'=>($user->canDeposit==1)?'on':'off',
                'canSell'=>($user->canSell==1)?'on':'off',
                'canBuy'=>($user->canBuy==1)?'on':'off',
                'canSwap'=>($user->canSwap==1)?'on':'off',
                'refBy'=>$user->refBy,
                'accountBalance'=>$user->balance,
                'refBalance'=>$user->refBalance,
                'enrolledInSignal'=>($user->enrolledInSignal==1)?'yes':'no',
                'packageEnrolled'=>(!empty($package))?$package->name:'none',
                'notification'=>($user->notification==1)?'active':'inactive',
                'timeRenewPayment'=>($user->enrolledInSignal==1)?date('d-m-Y',$user->timeRenewPayment):'none',
                'addressProof'=>asset('user/photo/'.$user->proofOfAddress),
                'referralEarning'=>$datRefEarn
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    public function getUserDetails($id)
    {
        $user = User::where('isAdmin','!=',1)->where('id',$id)->first();
        if (empty($user)){
            return $this->sendError('account.error',['error'=>'No data found']);
        }
        $package = SignalPackage::where('id',$user->packageEnrolled)->first();

        $refEarnings = ReferralEarning::where('referrer',$user->id)->get();
        $datRefEarn = [];

        foreach ($refEarnings as $refEarning) {
            $dataRefEarn = [
                'amount'=>$refEarning->amount,
                'timeEarned'=>strtotime($refEarning->created_at)
            ];

            $datRefEarn[]=$dataRefEarn;
        }
        $data = [
            'id'=>$user->id,
            'reference'=>$user->userRef,
            'email'=>$user->email,
            'registrationIp'=>$user->regIp,
            'country'=>$user->country,
            'phoneCode'=>$user->phoneCode,
            'phone'=>$user->phone,
            'state'=>$user->state,
            'city'=>$user->city,
            'address'=>$user->address,
            'accountCurrency'=>$user->mainCurrency,
            'twoFactor'=>($user->twoFactor==1)?'on':'off',
            'emailVerified'=>($user->emailVerified==1)?'yes':'no',
            'phoneVerified'=>($user->phoneVerified==1)?'yes':'no',
            'accountVerified'=>($user->accountVerified==1)?'verified':'unverified',
            'photo'=>asset('user/photo/'.$user->photo),
            'canSendCrypto'=>($user->canSend==1)?'on':'off',
            'canDeposit'=>($user->canDeposit==1)?'on':'off',
            'canSell'=>($user->canSell==1)?'on':'off',
            'canBuy'=>($user->canBuy==1)?'on':'off',
            'canSwap'=>($user->canSwap==1)?'on':'off',
            'refBy'=>$user->refBy,
            'accountBalance'=>$user->balance,
            'refBalance'=>$user->refBalance,
            'enrolledInSignal'=>($user->enrolledInSignal==1)?'yes':'no',
            'packageEnrolled'=>(!empty($package))?$package->name:'none',
            'notification'=>($user->notification==1)?'active':'inactive',
            'timeRenewPayment'=>($user->enrolledInSignal==1)?date('d-m-Y',$user->timeRenewPayment):'none',
            'referralEarning'=>$datRefEarn
        ];
        return $this->sendResponse($data, 'retrieved');
    }

    public function getUserWithdrawals($user,$index=0)
    {
        $withdrawals = Withdrawal::where('user',$user)->offset($index*50)->limit(50)->get();
        if ($withdrawals->count()<1){
            return $this->sendError('withdrawals.error',['error'=>'Nothing found']);
        }
        $dataCo=[];
        foreach ($withdrawals as $withdrawal) {
            $coin = Coin::where('asset',$withdrawal->asset)->first();
            $user = User::where('id',$withdrawal->user)->first();
            $data = [
                'id'=>$withdrawal->id,
                'address'=>$withdrawal->address,
                'asset'=>$withdrawal->asset,
                'memo'=>(empty($withdrawal->memo))?'':$withdrawal->memo,
                'status'=>($withdrawal->status==1)?'approved':'pending approval',
                'dateCreated'=>strtotime($withdrawal->created_at),
                'user'=>$user->name,'userId'=>$user->id,
                'userRef'=>$user->userRef,
                'coinName'=>$coin->name,
                'fiatEquivalent'=>$withdrawal->fiatAmount,
                'reference'=>$withdrawal->reference
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    public function getUserDeposits($user,$index=0)
    {
        $deposits = Deposit::where('user',$user)->offset($index*50)->limit(50)->get();
        if ($deposits->count()<1){
            return $this->sendError('withdrawals.error',['error'=>'Nothing found']);
        }
        $dataCo=[];
        foreach ($deposits as $deposit) {
            $coin = Coin::where('asset',$deposit->asset)->first();
            $rate = $this->getCryptoRate($coin->asset);
            $user = User::where('id',$deposit->user)->first();
            $data=[
                'amount'=>$deposit->amount,'asset'=>$deposit->asset,
                'name'=>$coin->name,'date'=>strtotime($deposit->created_at),
                'fiatEquivalent'=>$deposit->amount*$rate,'txId'=>$deposit->transHash,
                'memo'=>$deposit->memo,'user'=>$user->name,
                'depositId'=>$deposit->depositId
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function getUserSwaps($user,$index=0)
    {
        $swaps = Swap::where('user',$user)->offset($index*50)->limit(50)->get();
        if ($swaps->count()<1){
            return $this->sendError('withdrawals.error',['error'=>'Nothing found']);
        }
        $dataCo=[];
        foreach ($swaps as $swap) {
            $user = User::where('id', $swap->user)->first();
            switch ($swap->status) {
                case 1:
                    $status = 'completed';
                    break;
                case 2:
                    $status = 'pending';
                    break;
                case 3:
                    $status = 'cancelled';
                    break;
                default:
                    $status = 'pending approval';
                    break;
            }
            $data = [
                'id'=>$swap->id,
                'amountCredit' => $swap->amountCredit,
                'user' => $user->name,
                'from'=>$swap->assetFrom,
                'to'=>$swap->assetTo,
                'amountFrom'=>$swap->amountFrom,
                'amountTo'=>$swap->amountTo,
                'charge'=>$swap->charge,
                'date' => strtotime($swap->created_at),
                'status' => $status,
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function getUserPurchases($user,$index=0)
    {
        $purchases = Purchase::where('user',$user)->offset($index*50)->limit(50)->get();
        if ($purchases->count()<1){
            return $this->sendError('withdrawals.error',['error'=>'Nothing found']);
        }
        $dataCo=[];
        foreach ($purchases as $purchase) {
            $user = User::where('id', $purchase->user)->first();
            switch ($purchase->status) {
                case 1:
                    $status = 'completed';
                    break;
                case 2:
                    $status = 'pending';
                    break;
                case 3:
                    $status = 'cancelled';
                    break;
                default:
                    $status = 'pending approval';
                    break;
            }
            $data = [
                'id' => $purchase->id,
                'reference' => $purchase->reference,
                'cryptoAmount' => $purchase->amount,
                'asset' => $purchase->asset,
                'fiatAmount' => $purchase->fiatAmount,
                'fiat' => $purchase->fiat,
                'rateGiven' => $purchase->rate,
                'ngnRate' => $purchase->rateNGN,
                'charge' => $purchase->charge,
                'amountCredited' => $purchase->amountCredit,
                'status' => $status,
                'user' => $user->name,
                'dateInitiated' => $purchase->created_at

            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function getUserSales($user,$index=0)
    {
        $sales = Sale::where('user',$user)->offset($index*50)->limit(50)->get();
        $dataCo=[];
        foreach ($sales as $sale) {
            $user = User::where('id', $sale->user)->first();
            switch ($sale->status) {
                case 1:
                    $status = 'completed';
                    break;
                case 2:
                    $status = 'pending';
                    break;
                case 3:
                    $status = 'cancelled';
                    break;
                default:
                    $status = 'pending approval';
                    break;
            }
            $data = [
                'id' => $sale->id,
                'reference' => $sale->reference,
                'cryptoAmount' => $sale->amount,
                'asset' => $sale->asset,
                'fiatAmount' => $sale->fiatAmount,
                'fiat' => $sale->fiat,
                'rateGiven' => $sale->rate,
                'ngnRate' => $sale->rateNGN,
                'charge' => $sale->charge,
                'amountCredited' => $sale->amountCredit,
                'status' => $status,
                'user' => $sale->name,
                'dateInitiated' => $sale->created_at

            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function getUserSignalPayments($user,$index=0)
    {
        $payments = SignalEnrollmentPayment::where('user',$user)->offset($index*50)->limit(50)->get();
        $dataCo=[];
        foreach ($payments as $payment) {
            $user = User::where('id', $payment->user)->first();
            $package = SignalPackage::where('id',$payment->package)->first();
            switch ($payment->status) {
                case 1:
                    $status = 'completed';
                    break;
                case 2:
                    $status = 'pending approval';
                    break;
                case 3:
                    $status = 'cancelled';
                    break;
                default:
                    $status = 'pending';
                    break;
            }
            $data = [
                'id' => $payment->id,
                'reference' => $payment->reference,
                'amount' => $payment->amount,
                'amountPaid' => $payment->amountPaid,
                'fiat' => $payment->fiat,
                'bank' => $payment->bank,
                'accountName' => $payment->accountName,
                'accountNumber' => $payment->accountNumber,
                'amountCredited' => $payment->amountCredit,
                'status' => $status,
                'user' => $user->name,
                'dateInitiated' => $payment->created_at,
                'authorizedBy'=>$payment->authorizedBy,
                'signalPackage'=>$package->name
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function getUserFiatWithdrawals($user,$index=0)
    {
        $withdrawals = FiatWithdrawal::where('user',$user)->offset($index*50)->limit(50)->get();
        $dataCo=[];
        foreach ($withdrawals as $withdrawal) {
            $user = User::where('id', $withdrawal->user)->first();
            switch ($withdrawal->status) {
                case 1:
                    $status = 'completed';
                    break;
                case 2:
                    $status = 'pending payment';
                    break;
                case 3:
                    $status = 'cancelled';
                    break;
                default:
                    $status = 'pending approval';
                    break;
            }
            $data = [
                'amount' => $withdrawal->amount,
                'fiatAmount' => $withdrawal->fiatAmount,
                'amountCredit' => $withdrawal->amountCredit,
                'charge' => $withdrawal->charge,
                'bank' => $withdrawal->bank,
                'user' => $user->name,
                'accountName' => $withdrawal->accountName,
                'accountNumber' => $withdrawal->accountNumber,
                'date' => strtotime($withdrawal->created_at),
                'status' => $status,
                'authorizedBy' => $withdrawal->authorizedBy,
                'id'=>$withdrawal->id
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function getUserBanks($user,$index=0)
    {
        $banks = UserBank::where('user',$user)->offset($index*50)->limit(50)->get();
        if ($banks->count()<1){
            return $this->sendError('account.error',['error'=>'No data found']);
        }

        $dataCo=[];
        foreach ($banks as $bank) {
            $user = User::where('id',$bank->user)->first();
            $data = [
                'id'=>$bank->id,'bank'=>$bank->bank,
                'accountName'=>$bank->accountName,
                'accountNumber'=>$bank->accountNumber,
                'status'=>($bank->status==1)?'active':'inactive',
                'dateCreated'=>strtotime($bank->created_at),
                'user'=>$user->name,'userId'=>$user->id,'userRef'=>$user->userRef
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function getUserReferrals($user,$index=0)
    {
        $referrals = User::where('refBy',$user)->offset($index*50)->limit(50)->get();
        if ($referrals->count()<1){
            return $this->sendError('account.error',['error'=>'No data found']);
        }

        $dataCo=[];
        foreach ($referrals as $referral) {
            $user = User::where('id',$referral->user)->first();
            $data = [
                'id'=>$referral->id,'name'=>$referral->name,
                'userRef'=>$user->userRef
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    public function topUpUserBalance(Request $request): \Illuminate\Http\JsonResponse
    {
        $admin = Auth::user();
        $web = GeneralSetting::find(1);
        $validator = Validator::make($request->all(),[
            'amount'=>['required','numeric'],
            'id'=>['required','numeric'],
            'pin'=>['required'],
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()]);
        }
        $input=$validator->validated();
        //check if the pin is accurate
        if(!Hash::check($input['pin'],$admin->transPin)){
            return $this->sendError('security.error',['error'=>'Invalid account Pin'],422);
        }

        $user = User::where('id',$input['id'])->first();
        if (empty($user)){
            return $this->sendError('validation.error',['error'=>'User not found']);
        }

        $dataBalance = [
            'balance'=>$user->balance+$input['amount']
        ];

        if (User::where('id',$user->id)->update($dataBalance)){
            $dataNotification = [
                'user'=>$admin->id,'title'=>'User Balance Top-up',
                'content'=>$user->name." account was credited with ".$web->mainCurrency.number_format($input['amount'],2),
                'showAdmin'=>1
            ];

            Notification::create($dataNotification);
            //mail to user
            $messageToUser = "
                Your account has been topped up with $".number_format($input['amount'],2).".
            ";
            $user->notify(new AdminMail($user,$messageToUser,'Account Top-up'));
            //mail to super admin
            $superAdmin = User::where(['isAdmin'=>1,'role'=>1])->first();
            if (!empty($superAdmin)){
                $messageToAdmin = "
                    An account with reference <b>".$user->userRef."</b> and name <b> ".$user->name."</b> has been topped
                    up with <b>$".number_format($input['amount'],2)."</b> by
                    <b>".$admin->name."</b>.
                ";
                $superAdmin->notify(new AdminMail($superAdmin,$messageToAdmin,'Account Top-up'));
            }

            $dataResponse = [
                'name'=>$user->name
            ];
            return $this->sendResponse($dataResponse,'Balance topped up.');
        }
        return $this->sendError('validation.error',['error'=>'Something went wrong']);
    }

    public function subtractUserBalance(Request $request): \Illuminate\Http\JsonResponse
    {
        $admin = Auth::user();
        $web = GeneralSetting::find(1);
        $validator = Validator::make($request->all(),[
            'amount'=>['required','numeric'],
            'id'=>['required','numeric'],
            'pin'=>['required'],
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()]);
        }
        $input=$validator->validated();

        //check if the pin is accurate
        if(!Hash::check($input['pin'],$admin->transPin)){
            return $this->sendError('security.error',['error'=>'Invalid account Pin'],422);
        }

        $user = User::where('id',$input['id'])->first();
        if (empty($user)){
            return $this->sendError('validation.error',['error'=>'User not found']);
        }

        $dataBalance = [
            'balance'=>$user->balance-$input['amount']
        ];

        if (User::where('id',$user->id)->update($dataBalance)){

            $dataNotification = [
                'user'=>$admin->id,'title'=>'User Balance Subtraction',
                'content'=>$user->name." account was debited of $".number_format($input['amount'],2),
                'showAdmin'=>1
            ];

            Notification::create($dataNotification);
            //mail to super admin
            $superAdmin = User::where(['isAdmin'=>1,'role'=>1])->first();
            if (!empty($superAdmin)){
                $messageToAdmin = "
                    An account with reference <b>".$user->userRef."</b> and name <b> ".$user->name."</b> has been debitted
                    of <b>$".number_format($input['amount'],2)."</b> by
                    <b>".$admin->name."</b>.
                ";
                $superAdmin->notify(new AdminMail($superAdmin,$messageToAdmin,'Account Debit'));
            }

            $dataResponse = [
                'name'=>$user->name
            ];
            return $this->sendResponse($dataResponse,'Balance debited.');
        }
        return $this->sendError('validation.error',['error'=>'Something went wrong']);
    }

    public function updateUserSettings(Request $request)
    {

        $admin = Auth::user();
        $web = GeneralSetting::find(1);
        $validator = Validator::make($request->all(),[
            'canSend'=>['required','integer'],
            'canDeposit'=>['required','integer'],
            'canSell'=>['required','integer'],
            'canBuy'=>['required','integer'],
            'canSwap'=>['required','integer'],
            'status'=>['required','integer'],
            'pin'=>['required'],
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()]);
        }
        $input=$validator->validated();

        //check if the pin is accurate
        if(!Hash::check($input['pin'],$admin->transPin)){
            return $this->sendError('security.error',['error'=>'Invalid account Pin'],422);
        }

        $user = User::where('id',$input['id'])->first();
        if (empty($user)){
            return $this->sendError('validation.error',['error'=>'User not found']);
        }

        $dataUser = [
            'status'=>$input['status'],
            'canSend'=>$input['canSend'],
            'canDeposit'=>$input['canDeposit'],
            'canSell'=>$input['canSell'],
            'canBuy'=>$input['canBuy'],
            'canSwap'=>$input['canSwap'],
        ];
        if (User::where('id',$user->id)->update($dataUser)){
            $dataNotification=[

                'user'=>$admin->id,
                'title'=>'User Update','content'=>$user->name.' status and usage power was currently updated by admin '.
                    $admin->name,
                'showAdmin'=>1
            ];
            Notification::create($dataNotification);
            //mail to super admin
            $superAdmin = User::where(['isAdmin'=>1,'role'=>1])->first();
            if (!empty($superAdmin)){
                $messageToAdmin = "
                    An account with reference <b>".$user->userRef."</b> and name <b> ".$user->name."</b> status and usage
                    privilege was recently updated by <b>".$admin->name."</b>.
                ";
                $superAdmin->notify(new AdminMail($superAdmin,$messageToAdmin,'User Account privilege Update'));
            }
            $dataResponse=[
                'name'=>$user->name
            ];
            return $this->sendResponse($dataResponse,'user successfully updated');
        }
        return $this->sendError('user.error',['error'=>'Something went wrong']);
    }
    public function verifyUser(Request $request)
    {
        $admin = Auth::user();
        $web = GeneralSetting::find(1);
        $validator = Validator::make($request->all(),[
            'docId'=>['required','numeric'],
            'pin'=>['required'],
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()]);
        }
        $input=$validator->validated();

        //check if the pin is accurate
        if(!Hash::check($input['pin'],$admin->transPin)){
            return $this->sendError('security.error',['error'=>'Invalid account Pin'],422);
        }

        $verificationExists = UserVerification::where('id',$input['docId'])->first();

        if (empty($verificationExists)){
            return $this->sendError('verification.error',['error'=>'Invalid document'],422);
        }

        $user = User::where('id',$verificationExists->user)->first();
        if (empty($user)){
            return $this->sendError('validation.error',['error'=>'User not found']);
        }

        $dataUser = [
            'accountVerified'=>1
        ];

        $dataDoc = [
            'status'=>1,
            'approvedBy'=>$admin->name
        ];
        if (UserVerification::where('id',$verificationExists->id)->update($dataDoc)){
            User::where('id',$user->id)->update($dataUser);

            $dataNotificationUser=[
                'title'=>'Account Verified',
                'content'=>'Your documents were approved',
                'user'=>$user->id
            ];
            Notification::create($dataNotificationUser);

            $dataNotificationAdmin=[
                'title'=>'User Account Verification',
                'content'=>$user->name.' verification documents were approved by '.$admin->name,
                'user'=>$admin->id
            ];
            Notification::create($dataNotificationAdmin);
            //send email to user
            $messageToUser = "
                Your verification submission has been approved. You can now enjoy ".env('APP_NAME')." to the fullest.
            ";
            $user->notify(new AdminMail($user,$messageToUser,'Verification approved.'));

            //send mail to super admin
            $superAdmin = User::where(['isAdmin'=>1,'role'=>1])->first();
            if (!empty($superAdmin)){
                $messageToAdmin = "
                    An account with reference <b>".$user->userRef."</b> and name <b> ".$user->name."</b> verification
                     documents were recently approved by <b>".$admin->name."</b>.
                ";
                $superAdmin->notify(new AdminMail($superAdmin,$messageToAdmin,'User Account Verification Approval'));
            }
            $dataResponse=[
                'name'=>$user->name
            ];
            return $this->sendResponse($dataResponse,'documents successfully verified');
        }
        return $this->sendError('user.error',['error'=>'Something went wrong']);
    }
    public function rejectUserVerification(Request $request)
    {
        $admin = Auth::user();
        $web = GeneralSetting::find(1);
        $validator = Validator::make($request->all(),[
            'docId'=>['required','numeric'],
            'pin'=>['required'],
            'note'=>['required']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()]);
        }
        $input=$validator->validated();

        //check if the pin is accurate
        if(!Hash::check($input['pin'],$admin->transPin)){
            return $this->sendError('security.error',['error'=>'Invalid account Pin'],422);
        }

        $verificationExists = UserVerification::where('id',$input['docId'])->first();

        if (empty($verificationExists)){
            return $this->sendError('verification.error',['error'=>'Invalid document'],422);
        }

        $user = User::where('id',$verificationExists->user)->first();
        if (empty($user)){
            return $this->sendError('validation.error',['error'=>'User not found']);
        }

        $dataUser = [
            'accountVerified'=>2
        ];

        $dataDoc = [
            'status'=>3,
            'approvedBy'=>$admin->name
        ];
        if (UserVerification::where('id',$verificationExists->id)->update($dataDoc)){
            User::where('id',$user->id)->update($dataUser);

            $dataNotificationUser=[
                'title'=>'Documents rejected',
                'content'=>'Your documents were rejected due to '.$input['note'],
                'user'=>$user->id
            ];
            Notification::create($dataNotificationUser);

            $dataNotificationAdmin=[
                'title'=>'User Account Verification',
                'content'=>$user->name.' verification documents were rejected by '.$admin->name.' due to '.$input['note'],
                'user'=>$admin->id
            ];
            Notification::create($dataNotificationAdmin);
            //send email to user
            $messageToUser = "
                Your verification submission has been rejected due to some reasons: <br>".$input['note']."
            ";
            $user->notify(new AdminMail($user,$messageToUser,'Verification Document rejected.'));

            //send mail to super admin
            $superAdmin = User::where(['isAdmin'=>1,'role'=>1])->first();
            if (!empty($superAdmin)){
                $messageToAdmin = "
                    An account with reference <b>".$user->userRef."</b> and name <b> ".$user->name."</b> verification
                     documents were recently rejected by <b>".$admin->name."</b> due to ".$input['note']."
                ";
                $superAdmin->notify(new AdminMail($superAdmin,$messageToAdmin,'User Account Verification Rejected'));
            }
            $dataResponse=[
                'name'=>$user->name
            ];
            return $this->sendResponse($dataResponse,'documents rejected.');
        }
        return $this->sendError('user.error',['error'=>'Something went wrong']);
    }
    public function userVerificationDocument($user)
    {
        $document = UserVerification::where('user',$user)->first();
        if (empty($document)){
            return $this->sendError('document.error',['error'=>'No data found']);
        }
        $data=[
            'idType'=>$document->idType,'idNumber'=>$document->idNumber,
            'image'=>asset('user/ids/'.$document->image),
            'dateCreated'=>$document->dateCreated,'expiryDate'=>$document->expiryDate,
            'approvedBy'=>$document->approvedBy,
            'note'=>$document->note
        ];
        return $this->sendResponse($data,'retrieved');
    }
    //activate notification
    public function activateNotification($user)
    {
        $user = User::where('id',$user)->first();
        if (!empty($user)){

            $data=[
                'notification'=>1
            ];
            if (User::where('id',$user->id)->update($data)){

                $dataResponse=[
                    'name'=>$user->name
                ];
                return $this->sendResponse($dataResponse,'notification activated.');

            }
            return $this->sendError('user.error',['error'=>'Something went wrong'],421);
        }
        return $this->sendError('user.error',['error'=>'User not found'],421);
    }
    //deactivate notification
    public function deactivateNotification($user)
    {
        $user = User::where('id',$user)->first();
        if (!empty($user)){

            $data=[
                'notification'=>2
            ];
            if (User::where('id',$user->id)->update($data)){

                $dataResponse=[
                    'name'=>$user->name
                ];
                return $this->sendResponse($dataResponse,'notification activated.');

            }
            return $this->sendError('user.error',['error'=>'Something went wrong'],421);
        }
        return $this->sendError('user.error',['error'=>'User not found'],421);
    }

}
