<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Notification;
use App\Models\SystemAccount;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Notifications\AdminMail;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SystemAccounts extends BaseController
{
    use PubFunctions;
    public function landingPage()
    {

    }

    public function getAccounts()
    {
        $wallets = SystemAccount::get();

        if ($wallets->count()<1){
            return $this->sendError('account.error',['error'=>'Nothing found']);
        }

        $dataCo=[];
        foreach ($wallets as $wallet) {
            $coin = Coin::where('asset',$wallet->asset)->first();
            $data = [
                'id'=>$wallet->id,
                'address'=>$wallet->address,
                'asset'=>$wallet->asset,
                'balance'=>$wallet->availableBalance,
                'memo'=>(empty($wallet->memo))?'':$wallet->memo,
                'status'=>($wallet->status==1)?'active':'inactive',
                'dateCreated'=>strtotime($wallet->created_at),
                'coinName'=>$coin->name,
                'withdrawalType'=>($wallet->canSend==1)?'direct':'individual'
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    public function accountDetails($id)
    {
        $wallet = SystemAccount::where('id',$id)->first();

        $coin = Coin::where('asset',$wallet->asset)->first();
        $data = [
            'id'=>$wallet->id,
            'address'=>$wallet->address,
            'asset'=>$wallet->asset,
            'balance'=>$wallet->availableBalance,
            'memo'=>(empty($wallet->memo))?'':$wallet->memo,
            'status'=>($wallet->status==1)?'active':'inactive',
            'dateCreated'=>strtotime($wallet->created_at),
            'coinName'=>$coin->name,
            'withdrawalType'=>($wallet->canSend==1)?'direct':'individual'
        ];
        return $this->sendResponse($data, 'retrieved');
    }

    public function doWithdrawal(Request $request)
    {
        $admin = Auth::user();
        $validator = Validator::make($request->all(),[
            'address'=>['required','string'],
            'memo'=>['nullable','string'],
            'amount'=>['required','numeric'],
            'pin'=>['required'],
            'id'=>['required','numeric'],
            'asset'=>['required','numeric']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input = $validator->validated();
        //check the account pin sent
        $hashed = Hash::check($input['pin'],$admin->transPin);
        if (!$hashed){
            return $this->sendError('account.error',['error'=>'Invalid account pin'],401);
        }
        //get the wallet to withdraw from
        $wallet = SystemAccount::where('id',$input['id'])->first();

        if (!empty($wallet)) {
            if ($input['amount'] > $wallet->availableBalance) {
                return $this->sendError('wallet.error', ['error' => 'Insufficient balance for this withdrawal'],
                    401);
            }
            //check if the withdrawal can be individual or direct
            if ($wallet->canSend!=1){
                return $this->sendError('wallet.error', ['error' => 'Direct withdrawal from system account is not
                supported, please withdraw directly from individual wallets'],
                    401);
            }

            //get the coin
            $coin = Coin::where('asset', $input['asset'])->first();

            if ($coin->minSend >$input['amount']){
                return $this->sendError('wallet.error',
                    ['error' => 'Minimum Withdrawal allowed is '.$coin->minSend.''.$coin->asset],
                    401);
            }

            $dataBalance=[
                'availableBalance'=>$wallet->availableBalance - $input['amount']
            ];

            $rate = $this->getCryptoRate($coin->asset);

            $ref = $this->generateRef('withdrawals', 'reference');
            $dataWithdrawal = [
                'user' => $admin->id,
                'reference' => $ref,
                'asset' => $wallet->asset,
                'amount' => $input['amount'],
                'fiatAmount' => $input['amount'] * $rate,
                'accountId' => $wallet->accountId,
                'fee' => $coin->networkFee,
                'withdrawalType' => 3,
                'destination' => 'external',
                'addressTo' => $input['address'],
                'memo' => $input['memo'],
                'hasMemo' => $coin->hasMemo,
                'isSystem' => 1,
                'status' => 6,
                'approvedBy'=>'',
                'derivationKey'=>$wallet->derivationKey
            ];

            $withdrawal = Withdrawal::create($dataWithdrawal);
            if (!empty($withdrawal)) {
                SystemAccount::where('id',$wallet->id)->update($dataBalance);

                $dataNotification = [
                    'user' => $admin->id, 'title' => 'New System Withdrawal',
                    'content' => 'A new system withdrawal of ' . $input['amount'] . $input['asset'] . ' was initiated.',
                    'showAdmin' => 1
                ];
                $message = "
                A new system withdrawal has been initiated by " . $admin->name . " with reference " . $ref . ".
                <p>The sum of " . $input['amount'] . $input['asset'] . "</p> was withdrawn from the account pending
                approval.
            ";
                $superAdmin = User::where(['isAdmin' => 1, 'role' => 1])->first();
                if (!empty($superAdmin)) {
                    $superAdmin->notify(new AdminMail($superAdmin, $message, 'New System Withdrawal'));
                }
                Notification::create($dataNotification);
                $dataResponse = [
                    'id' => $withdrawal->id,
                    'reference' => $withdrawal->reference,
                    'amount' => $withdrawal->amount,
                    'fiatEquivalent' => $withdrawal->fiatAmount,
                    'status' => ($withdrawal->status == 1) ? 'completed' : 'processing',
                    'address' => $withdrawal->addressTo,
                ];
                return $this->sendResponse($dataResponse, 'withdrawal sent:processing.');
            }
            return $this->sendError('withdrawal.error', ['error' => 'Something went wrong. Try again']);
        }
        return $this->sendError('wallet.error', ['error' => 'Invalid wallet selected'], 401);
    }

    public function systemWithdrawals($index=0)
    {
        $withdrawals = Withdrawal::where('isSystem',1)->offset($index*50)->limit(50)->get();
        if ($withdrawals->count()<1){
            return $this->sendError('account.error',['error'=>'No data found']);
        }
        $dataCo=[];
        foreach ($withdrawals as $withdrawal) {
            $coin = Coin::where('asset',$withdrawal->asset)->first();
            $data = [
                'id'=>$withdrawal->id,
                'address'=>$withdrawal->address,
                'asset'=>$withdrawal->asset,
                'memo'=>(empty($withdrawal->memo))?'':$withdrawal->memo,
                'status'=>($withdrawal->status==1)?'approved':'pending approval',
                'dateCreated'=>strtotime($withdrawal->created_at),
                'coinName'=>$coin->name,
                'fiatEquivalent'=>$withdrawal->fiatAmount,
                'reference'=>$withdrawal->reference,
                'approvedBy'=>$withdrawal->approvedBy
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
        if ($admin->role!=1){
            return $this->sendError('account.error',['error'=>'unauthorized access'],401);
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
        if ($withdrawal->status ==4){
            return $this->sendError('withdrawal.error',['error'=>'Withdrawal already processing.'],422);
        }

        $dataWithdrawal = [
            'status'=>4,'approvedBy'=>$admin->name
        ];
        if (Withdrawal::where('id',$withdrawal->id)->update($dataWithdrawal)){
            //notification for admin
            $dataNotification=[
                'user'=>$admin->id,'title'=>'New System Withdrawal Approval',
                'content'=>'A withdrawal with reference '.$withdrawal->reference.' has been approved.',
                'showAdmin'=>1
            ];
            $message = "
                A system withdrawal with reference ".$withdrawal->reference." has been approved by the
                admin ".$admin->name."
            ";
            $superAdmin = User::where(['isAdmin'=>1,'role'=>1])->first();
            if (!empty($superAdmin)){
                $superAdmin->notify(new AdminMail($superAdmin,$message,'New Withdrawal approval'));
            }
            Notification::create($dataNotification);

            $dataResponse =[
                'id'=>$withdrawal->id,
                'reference'=>$withdrawal->reference,
                'amount'=>$withdrawal->amount,
                'fiatEquivalent'=>$withdrawal->fiatAmount,
                'status'=>'approved',
                'address'=>$withdrawal->addressTo,
            ];
            return $this->sendResponse($dataResponse,'withdrawal sent:processing.');

        }
        return $this->sendError('withdrawal.error',['error'=>'Something went wrong'],422);
    }
}
