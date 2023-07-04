<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\FiatDeposit;
use App\Models\GeneralSetting;
use App\Models\Notification;
use App\Models\SystemFiatAccount;
use App\Models\User;
use App\Notifications\AdminMail;
use App\Notifications\UserNotification;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class FiatDeposits extends BaseController
{
    use PubFunctions;
    //landing page
    public function landingPage()
    {
        return view('deposits.fiat');
    }
    //get all deposits
    public function getDeposits($index=0)
    {
        $deposits = FiatDeposit::offset($index*50)->limit(50)->get();
        $dataCo=[];
        foreach ($deposits as $deposit) {
            $data = $this->getDepositData($deposit);
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    //get a deposits
    public function getDepositId($id)
    {
        $deposit = FiatDeposit::where('id',$id)->first();

        $data = $this->getDepositData($deposit);
        return $this->sendResponse($data, 'retrieved');
    }
    //approve deposit
    public function approveDeposit(Request $request)
    {
        $admin = Auth::user();
        $web = GeneralSetting::find(1);

        $validator = Validator::make($request->all(),[
            'id'=>['numeric','required'],
            'pin'=>['required']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input = $validator->validated();

        //check if the pin is accurate
        if(!Hash::check($input['pin'],$admin->transPin)){
            return $this->sendError('security.error',['error'=>'Invalid account Pin'],422);
        }
        $deposit = FiatDeposit::where('id',$input['id'])->first();
        if (empty($deposit)) {
            return $this->sendError('deposit.error',['error'=>'invalid data'],422);
        }
        if ($deposit->status==1){
            return $this->sendError('deposit.error',['error'=>'Deposit already confirmed.'],422);
        }
        $user = User::where('id',$deposit->user)->first();

        $newBalance = $user->balance+$deposit->amount;
        $dataUser=['balance'=>$newBalance];
        $dataDeposit=[
            'status'=>1,
            'authorizedBy'=>$admin->name
        ];
        if (FiatDeposit::where('id',$input['id'])->update($dataDeposit)){
            User::where('id',$user->id)->update($dataUser);

            $dataNotify=[
                'title'=>'Deposit confirmation','content'=>'Your Deposit of
                '.$user->mainCurrency.number_format($deposit->amount,2).' has been confirmed',
                'user'=>$user->id
            ];
            Notification::create($dataNotify);

            $dataNotifyAdmin=[
                'title'=>'Deposit confirmation','content'=>'A Deposit of
                '.$web->mainCurrency.number_format($deposit->amount,2).' has been confirmed',
                'user'=>$admin->id,'showAdmin'=>1
            ];
            Notification::create($dataNotifyAdmin);
            //send mails to user
            $userMessage="
                Your deposit of ".$web->mainCurrency.number_format($deposit->amount,2)." has been credited to
                your account.<br> Your new Account Balance is ".$web->mainCurrency.number_format($newBalance,2)."
            ";
            $user->notify(new AdminMail($user,$userMessage,'Deposit Confirmation'));
            //send app notification
            $appMessage ="
                Your account has been credited with ".$web->mainCurrency.number_format($deposit->amount,2)."
            ";
            $user->notify(new UserNotification($user,$appMessage,'Account Credited'));

            //send mails to admin
            $superAdmin = User::where(['isAdmin'=>1,'role'=>1])->first();
            if (!empty($admin)){

                $message = "
                    A fiat Deposit of ".$user->mainCurrency.number_format($deposit->amount,2)." made
                    by ".$user->name." was just approved by ".$admin->name.". The amount has been credited to
                    the user account balance.
                ";
                $superAdmin->notify(new AdminMail($superAdmin,$message,'Deposit Approval on '.env('APP_NAME')));
            }
            $dataResponse = [
                'status'=>'approved'
            ];
            return $this->sendResponse($dataResponse,'deposit approved');
        }
        return $this->sendError('deposit.error',['error'=>'Something went wrong'],422);
    }
    public function cancelDeposit(Request $request)
    {
        $admin = Auth::user();
        $web = GeneralSetting::find(1);

        $validator = Validator::make($request->all(),[
            'id'=>['numeric','required'],
            'pin'=>['required']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input = $validator->validated();

        //check if the pin is accurate
        if(!Hash::check($input['pin'],$admin->transPin)){
            return $this->sendError('security.error',['error'=>'Invalid account Pin'],422);
        }
        $deposit = FiatDeposit::where('id',$input['id'])->first();
        if (empty($deposit)) {
            return $this->sendError('deposit.error',['error'=>'invalid data'],422);
        }
        $user = User::where('id',$deposit->user)->first();

        $dataDeposit=[
            'status'=>3,
            'authorizedBy'=>$admin->name
        ];
        if (FiatDeposit::where('id',$input['id'])->update($dataDeposit)){

            $dataNotify=[
                'title'=>'Deposit Cancellation','content'=>'Your Deposit of
                '.$user->mainCurrency.number_format($deposit->amount,2).' has been cancelled.',
                'user'=>$user->id
            ];
            Notification::create($dataNotify);

            $dataNotifyAdmin=[
                'title'=>'Deposit Cancellation','content'=>'A Deposit of
                '.$web->mainCurrency.number_format($deposit->amount,2).' has been cancelled',
                'user'=>$admin->id,'showAdmin'=>1
            ];
            Notification::create($dataNotifyAdmin);
            //send mails to user
            $userMessage="
                Your deposit of ".$web->mainCurrency.number_format($deposit->amount,2)." has been cancelled
                since we are unable to verify that this deposit was made.
            ";
            $user->notify(new AdminMail($user,$userMessage,'Deposit Cancellation'));
            //send mails to admin
            $superAdmin = User::where(['isAdmin'=>1,'role'=>1])->first();
            if (!empty($admin)){

                $message = "
                    A fiat Deposit of ".$user->mainCurrency.number_format($deposit->amount,2)." made
                    by ".$user->name." was just cancelled by ".$admin->name.". .
                ";
                $superAdmin->notify(new AdminMail($superAdmin,$message,'Deposit cancellation on '.env('APP_NAME')));
            }
            $dataResponse = [
                'status'=>'cancelled'
            ];
            return $this->sendResponse($dataResponse,'deposit cancelled');
        }
        return $this->sendError('deposit.error',['error'=>'Something went wrong'],422);
    }

    /**
     * @param $deposit
     * @return array
     */
    protected function getDepositData($deposit): array
    {
        $user = User::where('id', $deposit->user)->first();
        $systemAccount = SystemFiatAccount::where('reference', $deposit->systemAccount)->first();
        switch ($deposit->status) {
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
            'amount' => $deposit->amount,
            'amountPaid' => $deposit->amountPaid,
            'accountName' => $deposit->accountName,
            'accountNumber' => $deposit->accountNumber,
            'bank' => $deposit->bank,
            'user' => $user->name,
            'date' => strtotime($deposit->created_at),
            'systemAccount' => $systemAccount->accountNumber,
            'systemAccountName' => $systemAccount->accountName,
            'systemAccountBank' => $systemAccount->bank,
            'status' => $status,
            'authorizedBy' => $deposit->authorizedBy,
            'id'=>$deposit->id
        ];
        return $data;
    }
}
