<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\FiatWithdrawal;
use App\Models\GeneralSetting;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\AdminMail;
use App\Notifications\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class FiatWithdrawals extends BaseController
{
    public function landingPage()
    {
        return view('withdrawals.index');
    }
    //get all withdrawals
    public function getWithdrawals($index=0)
    {
        $withdrawals = FiatWithdrawal::offset($index*50)->limit(50)->get();
        $dataCo=[];
        foreach ($withdrawals as $withdrawal) {
            $data = $this->getWithdrawalData($withdrawal);
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    //get all withdrawal by ID
    public function getWithdrawalId($id)
    {
        $withdrawal = FiatWithdrawal::where('id',$id)->first();

        $data = $this->getWithdrawalData($withdrawal);

        return $this->sendResponse($data, 'retrieved');
    }
    public function approveWithdrawal(Request $request)
    {
        $admin = Auth::user();
        $web = GeneralSetting::find(1);

        $validator = Validator::make($request->all(),[
            'pin'=>['required'],
            'id'=>['required','integer']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input = $validator->validated();

        //validate the account pin
        $hashed = Hash::check($input['pin'],$admin->transPin);

        if (!$hashed){
            return $this->sendError('withdrawal.error',['error'=>'Invalid transaction pin'],422);
        }

        $withdrawalExists = FiatWithdrawal::where('id',$input['id'])->first();
        if (empty($withdrawalExists)){
            return $this->sendError('withdrawal.error',['error'=>'Withdrawal not found'],422);
        }
        if ($withdrawalExists->status==1){
            return $this->sendError('withdrawal.error',['error'=>'Withdrawal already confirmed'],422);
        }

        $dataWithdrawal = [
            'status'=>1,
            'authorizedBy'=>$admin->name
        ];

        $user = User::where('id',$withdrawalExists->user)->first();

        $messageToUser="
            Your withdrawal of ".$web->mainCurrency.number_format($withdrawalExists->amount,2)."
            has been processed, and credited to you.
        ";

        $superAdminMessage="
            A withdrawal of ".$web->mainCurrency.number_format($withdrawalExists->amount,2)."
            was processed, and credited to the user ".$user->name." by ".$admin->name."
        ";
        if (FiatWithdrawal::where('id',$withdrawalExists->id)->update($dataWithdrawal)){

            $dataNotify=[
                'title'=>'Withdrawal confirmation','content'=>'Your Withdrawal of
                '.$web->mainCurrency.number_format($withdrawalExists->amount,2).' has been confirmed',
                'user'=>$user->id
            ];
            Notification::create($dataNotify);

            $dataNotifyAdmin=[
                'title'=>'Withdrawal confirmation','content'=>'A withdrawal of
                '.$web->mainCurrency.number_format($withdrawalExists->amount,2).' has been confirmed',
                'user'=>$admin->id,'showAdmin'=>1
            ];
            Notification::create($dataNotifyAdmin);
            //send mail to the user
            $user->notify(new AdminMail($user,$messageToUser,'Withdrawal Completion'));
            //send app notification
            $appMessage ="Your fiat withdrawal with reference ".$withdrawalExists->reference." has been approved.";
            $user->notify(new UserNotification($user,$appMessage,'Fiat withdrawal approved'));


            $superAdmin=User::where(['isAdmin'=>1,'role'=>1])->first();
            if (!empty($superAdmin)){
                $superAdmin->notify(new AdminMail($superAdmin,$superAdminMessage,'Fiat withdrawal confirmation'));
            }

            $dataResponse=[
                'message'=>'Sent'
            ];
            return $this->sendResponse($dataResponse,'process successful.');
        }
        return $this->sendError('withdrawal.error',['error'=>'Something went wrong.']);
    }

    /**
     * @param $withdrawal
     * @return array
     */
    protected function getWithdrawalData($withdrawal): array
    {
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
            'rate'=>$withdrawal->rate,
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
        return $data;
    }
}
