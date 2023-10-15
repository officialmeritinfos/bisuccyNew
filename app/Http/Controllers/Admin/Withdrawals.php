<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Notification;
use App\Models\User;
use App\Models\Withdrawal;
use App\Notifications\AdminMail;
use App\Notifications\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Withdrawals extends BaseController
{
    public function landingPage()
    {
        return view('purchases.index');        
    }

    public function getWithdrawals($index=0)
    {
        $withdrawals = Withdrawal::where('isSystem','!=',1)->offset($index*50)->limit(50)->get();
        if ($withdrawals->count()<1){
            return $this->sendError('account.error',['error'=>'No data found']);
        }
        $dataCo=[];
        foreach ($withdrawals as $withdrawal) {
            $user = User::where('id',$withdrawal->user)->first();
            $coin = Coin::where('asset',$withdrawal->asset)->first();
            if ($withdrawal->destination!='external'){
                $recipient = User::where('userRef',$withdrawal->destination)->first();
            }
            switch ($withdrawal->status){
                case 1:
                    $status='completed';
                    break;
                case 2:
                    $status='pending';
                    break;
                case 3:
                    $status='failed';
                    break;
                default:
                    $status='queued';
                    break;
            }
            $data = [
                'id'=>$withdrawal->id,
                'address'=>$withdrawal->addressTo,
                'asset'=>$withdrawal->asset,
                'memo'=>(empty($withdrawal->memo))?'':$withdrawal->memo,
                'status'=>$status,
                'dateCreated'=>strtotime($withdrawal->created_at),
                'user'=>$user->name,'userId'=>$user->id,
                'userRef'=>$user->userRef,
                'coinName'=>$coin->name,
                'fiatEquivalent'=>$withdrawal->fiatAmount,
                'reference'=>$withdrawal->reference,
                'network'=>$withdrawal->network,
                'withdrawalType'=>($withdrawal->withdrawalType==2)?'external':'internal',
                'destination'=>($withdrawal->destination=='external')?'external':$recipient->name,
                'fee'=>$withdrawal->fee,
                'balanceAfter'=>$withdrawal->balance,
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function getWithdrawalByUser($user,$index=0)
    {
        $withdrawals = Withdrawal::where(['isSystem'=>2,'user'=>$user])->offset($index*50)->limit(50)->get();
        if ($withdrawals->count()<1){
            return $this->sendError('account.error',['error'=>'No data found']);
        }
        $dataCo=[];
        foreach ($withdrawals as $withdrawal) {
            $user = User::where('id',$withdrawal->user)->first();
            $coin = Coin::where('asset',$withdrawal->asset)->first();

            if ($withdrawal->destination!='external'){
                $recipient = User::where('userRef',$withdrawal->destination)->first();
            }
            switch ($withdrawal->status){
                case 1:
                    $status='completed';
                    break;
                case 2:
                    $status='pending';
                    break;
                case 3:
                    $status='failed';
                    break;
                default:
                    $status='queued';
                    break;
            }

            $data = [
                'id'=>$withdrawal->id,
                'address'=>$withdrawal->address,
                'asset'=>$withdrawal->asset,
                'memo'=>(empty($withdrawal->memo))?'':$withdrawal->memo,
                'status'=>$status,
                'dateCreated'=>strtotime($withdrawal->created_at),
                'user'=>$user->name,'userId'=>$user->id,
                'userRef'=>$user->userRef,
                'coinName'=>$coin->name,
                'fiatEquivalent'=>$withdrawal->fiatAmount,
                'reference'=>$withdrawal->reference,
                'network'=>$withdrawal->network,
                'withdrawalType'=>($withdrawal->withdrawalType==2)?'external':'internal',
                'destination'=>($withdrawal->destination=='external')?'external':$recipient->name,
                'fee'=>$withdrawal->fee
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function approveWithdrawal(Request $request)
    {
        $admin = Auth::user();

        $validator = Validator::make($request->all(),[
            'id'=>['required','numeric'],
            'pin'=>['required']
        ])->stopOnFirstFailure();
        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input=$validator->validated();

        $hashed = Hash::check($input['pin'],$admin->transPin);
        if (!$hashed){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        //get the withdrawal
        $withdrawal = Withdrawal::where('id',$input['id'])->first();
        if (!empty($withdrawal)){
            return $this->sendError('withdrawal.error',['error'=>'Invalid withdrawal'],422);
        }
        if ($withdrawal->status ==1){
            return $this->sendError('withdrawal.error',['error'=>'Withdrawal already approved.'],422);
        }
        if ($withdrawal->status ==3){
            return $this->sendError('withdrawal.error',['error'=>'Withdrawal already cancelled.'],422);
        }
        $user = User::where('id',$withdrawal->user)->first();

        $dataWithdrawal = [
            'status'=>1,'approvedBy'=>$admin->name
        ];
        if (Withdrawal::where('id',$withdrawal->id)->update($dataWithdrawal)){
            //send app notification for user
            $appMessage ="Your crypto withdrawal with reference ".$withdrawal->reference." has been completed";
            $user->notify(new UserNotification($user,$appMessage,'Withdrawal completed'));
            //notification for admin
            $dataNotification=[
                'user'=>$admin->id,'title'=>'New Withdrawal Approval',
                'content'=>'A withdrawal with reference '.$withdrawal->reference.' has been approved.',
                'showAdmin'=>1
            ];
            $message = "
                A withdrawal with reference ".$withdrawal->reference." has been approved by the admin ".$admin->name."
            ";
            $superAdmin = User::where(['isAdmin'=>1,'role'=>1])->first();
            if (!empty($superAdmin)){
                $superAdmin->notify(new AdminMail($superAdmin,$message,'New Withdrawal approval'));
            }
            Notification::create($dataNotification);
            //notification for user
            $dataNotificationUser=[
                'user'=>$user->id,'title'=>'Withdrawal completed',
                'content'=>'Your withdrawal with reference '.$withdrawal->reference.' has been completed.',
                'showAdmin'=>2
            ];
            Notification::create($dataNotificationUser);

            $dataResponse =[
                'id'=>$withdrawal->id,
                'reference'=>$withdrawal->reference,
                'amount'=>$withdrawal->amount,
                'fiatEquivalent'=>$withdrawal->fiatAmount,
                'status'=>'completed',
                'address'=>$withdrawal->addressTo,
            ];
            return $this->sendResponse($dataResponse,'withdrawal approved.');

        }
        return $this->sendError('withdrawal.error',['error'=>'Something went wrong'],422);
    }
}
