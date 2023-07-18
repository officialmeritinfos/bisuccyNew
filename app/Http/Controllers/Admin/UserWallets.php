<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Deposit;
use App\Models\Notification;
use App\Models\Permission;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Notifications\AdminMail;
use App\Notifications\DepositMail;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserWallets extends BaseController
{
    use PubFunctions;
    public function landingPage()
    {

    }

    public function getWallets($index=0)
    {
        $wallets = Wallet::offset($index*50)->limit(50)->get();
        if ($wallets->count()<1){
            return $this->sendError('account.error',['error'=>'No data found']);
        }

        $dataCo=[];
        foreach ($wallets as $wallet) {
            $user = User::where('id',$wallet->user)->first();
            $coin = Coin::where('asset',$wallet->asset)->first();
            $data = [
                'id'=>$wallet->id,
                'address'=>$wallet->address,
                'asset'=>$wallet->asset,
                'balance'=>$wallet->availableBalance,
                'mainBalance'=>$wallet->mainBalance,
                'memo'=>(empty($wallet->memo))?'':$wallet->memo,
                'status'=>($wallet->status==1)?'active':'inactive',
                'dateCreated'=>strtotime($wallet->created_at),
                'user'=>$user->name,'userId'=>$user->id,
                'userRef'=>$user->userRef,
                'coinName'=>$coin->name
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    public function getUserWallets($user,$index=0)
    {
        $wallets = Wallet::where('user',$user)->offset($index*50)->limit(50)->get();
        if ($wallets->count()<1){
            return $this->sendError('account.error',['error'=>'No data found']);
        }

        $dataCo=[];
        foreach ($wallets as $wallet) {
            $user = User::where('id',$wallet->user)->first();
            $coin = Coin::where('asset',$wallet->asset)->first();
            $data = [
                'id'=>$wallet->id,
                'address'=>$wallet->address,
                'asset'=>$wallet->asset,
                'balance'=>$wallet->availableBalance,
                'mainBalance'=>$wallet->mainBalance,
                'memo'=>(empty($wallet->memo))?'':$wallet->memo,
                'status'=>($wallet->status==1)?'active':'inactive',
                'dateCreated'=>strtotime($wallet->created_at),
                'user'=>$user->name,'userId'=>$user->id,
                'userRef'=>$user->userRef,
                'coinName'=>$coin->name
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function walletDetails($id)
    {
        $wallet = Wallet::where('id',$id)->first();
        if (empty($wallet)){
            return $this->sendError('account.error',['error'=>'No data found']);
        }

        $user = User::where('id',$wallet->user)->first();
        $coin = Coin::where('asset',$wallet->asset)->first();
        $data = [
            'id'=>$wallet->id,
            'address'=>$wallet->address,
            'asset'=>$wallet->asset,
            'balance'=>$wallet->availableBalance,
            'mainBalance'=>$wallet->mainBalance,
            'memo'=>(empty($wallet->memo))?'':$wallet->memo,
            'status'=>($wallet->status==1)?'active':'inactive',
            'dateCreated'=>strtotime($wallet->created_at),
            'user'=>$user->name,'userId'=>$user->id,
            'userRef'=>$user->userRef,
            'coinName'=>$coin->name
        ];

        return $this->sendResponse($data, 'retrieved');
    }
    public function walletDeposits($id,$index=0)
    {
        $wallet = Wallet::where('id',$id)->first();
        if (empty($wallet)){
            return $this->sendError('account.error',['error'=>'No data found']);
        }

        $deposits = Deposit::where(['asset'=>$wallet->asset,'user'=>$wallet->user])
            ->offset($index*50)->limit(50)->get();

        $dataCo=[];
        foreach ($deposits as $deposit) {
            $coin = Coin::where('asset',$deposit->asset)->first();
            $rate = $this->getCryptoRate($coin->asset);
            $user = User::where('id',$deposit->user)->first();
            $data=[
                'amount'=>$deposit->amount,'asset'=>$deposit->asset,
                'name'=>$coin->name,'date'=>strtotime($deposit->created_at),
                'fiatEquivalent'=>$deposit->amount*$rate,'txId'=>$deposit->transHash,
                'blockHeight'=>$deposit->blockHeight,'blockHash'=>$deposit->blockHash,
                'memo'=>$deposit->memo,'user'=>$user->name
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function walletWithdrawals($id,$index=0)
    {
        $wallet = Wallet::where('id',$id)->first();
        if (empty($wallet)){
            return $this->sendError('account.error',['error'=>'No data found']);
        }

        $withdrawals = Withdrawal::where(['asset'=>$wallet->asset,'user'=>$wallet->user,'isSystem'=>2])
            ->offset($index*50)->limit(50)->get();

        $dataCo=[];
        foreach ($withdrawals as $withdrawal) {
            $coin = Coin::where('asset',$withdrawal->asset)->first();
            $rate = $this->getCryptoRate($withdrawal->asset);
            $user = User::where('id',$withdrawal->user)->first();
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
            $data=[
                'amount'=>$withdrawal->amount,
                'asset'=>$withdrawal->asset,
                'name'=>$coin->name,
                'date'=>strtotime($withdrawal->created_at),
                'fiatEquivalent'=>$withdrawal->amount*$rate,
                'reference'=>$withdrawal->reference,
                'transHash'=>$withdrawal->transHash,
                'memo'=>$withdrawal->memo,
                'user'=>$user->name,
                'fee'=>$withdrawal->fee,
                'destination'=>($withdrawal->destination=='external')?'external':$recipient->name,
                'balanceAfter'=>$withdrawal->balance,
                'status'=>$status
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    //withdraw from the wallet
    public function withdrawFromWallet(Request $request)
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
        $wallet = Wallet::where('id',$input['id'])->first();
        if (empty($wallet)){
            return $this->sendError('wallet.error',['error'=>'Invalid wallet selected'],401);
        }
        if ($input['amount']>$wallet->mainBalance){
            return $this->sendError('wallet.error',['error'=>'Insufficient balance for this withdrawal'],401);
        }
        //get the coin and check against the minimum withdrawal
        $coin = Coin::where('asset',$input['asset'])->first();
        if ( $input['amount']<$coin->minSend){
            return $this->sendError('wallet.error',
                ['error' => 'Minimum Withdrawal allowed is '.$coin->minSend.''.$coin->asset],
                401);
        }
        $rate = $this->getCryptoRate($coin->asset);

        $ref = $this->generateRef('withdrawals','reference');
        $dataWithdrawal =[
            'user'=>$admin->id,
            'mainUser'=>$wallet->user,
            'reference'=>$ref,
            'asset'=>$wallet->asset,
            'amount'=>$input['amount'],
            'fiatAmount'=>$input['amount']*$rate,
            'accountId'=>$wallet->accountId,
            'fee'=>$coin->networkFee,
            'withdrawalType'=>3,
            'destination'=>'external',
            'addressTo'=>$input['address'],
            'memo'=>$input['memo'],
            'hasMemo'=>$coin->hasMemo,
            'isSystem'=>1,
            'status'=>6,
            'derivationKey'=>$wallet->derivationKey
        ];

        $dataBalance=[
            'mainBalance'=>$wallet->mainBalance-$input['amount'],
        ];

        $withdrawal = Withdrawal::create($dataWithdrawal);
        if (!empty($withdrawal)){
            Wallet::where('id',$wallet->id)->update($dataBalance);
            $dataNotification=[
                'user'=>$admin->id,'title'=>'New System Withdrawal',
                'content'=>'A new system withdrawal of '.$input['amount'].$input['asset'].' was initiated.',
                'showAdmin'=>1
            ];
            $message = "
                A new system withdrawal has been initiated by ".$admin->name." with reference ".$ref.".
                <p>The sum of ".$input['amount'].$input['asset']."</p> was withdrawn from the account pending
                approval.
            ";
            $superAdmin = User::where(['isAdmin'=>1,'role'=>1])->first();
            if (!empty($superAdmin)){
                $superAdmin->notify(new AdminMail($superAdmin,$message,'New System Withdrawal'));
            }
            Notification::create($dataNotification);
            $dataResponse =[
                'id'=>$withdrawal->id,
                'reference'=>$withdrawal->reference,
                'amount'=>$withdrawal->amount,
                'fiatEquivalent'=>$withdrawal->fiatAmount,
                'status'=>($withdrawal->status==1)?'completed':'processing',
                'address'=>$withdrawal->addressTo,
            ];
            return $this->sendResponse($dataResponse,'withdrawal sent.');
        }
        return $this->sendError('withdrawal.error',['error'=>'Something went wrong. Try again']);
    }
    //calculate gas fee for withdrawing from wallet
    public function calculateGasFees(Request $request)
    {
        $gateway = new \App\Regular\Wallet();

        $validator =Validator::make($request->all(), [
            'asset'=>['required',Rule::in(['ETH','USDT','BUSD_BSC'])],
            'addressTo'=>['required'],
            'walletFrom'=>['required','numeric']
        ])->stopOnFirstFailure();
        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        $input=$validator->validated();

        $coin = Coin::where('asset',$input['asset'])->first();

        switch ($input['asset']){
            case 'ETH':
                $chain ='ETH';
                $type=2;
            case 'USDT':
                $chain='ETH';
                $type=1;
                break;
            default:
                $chain='BSC';
                $type=1;
        }
        $wallet = Wallet::where('id',$input['walletFrom'])->first();

        if ($type==1){
            $data=[
                'chain'=>$chain,
                'type'=>"TRANSFER_ERC20",
                'sender'=>$wallet->address,
                'recipient'=>$input['addressTo'],
                'contractAddress'=>$coin->contractAddress,
                'amount'=>Str::remove(',',number_format($input['amount'],6))
            ];

            //send the request to query for the gas limit and gas price
            $response = $gateway->estimateGasFee($data);
            if ($response->ok()){
                $data = $response->json();

                $gasPrice =$data['gasPrice'] ;
                $gasLimit = $data['gasLimit'];
                //to get the ether amount, we multiply and devide by 1G
                $fee = ($gasPrice*$gasLimit)/1000000000;

                $dataResponse = [
                    'fee'=>$fee
                ];
                return $this->sendResponse($dataResponse,'fees retrieved');
            }
            return $this->sendError('fee.error',['error'=>'Unable to estimate gas fees'],321);
        }else{
            $data=[
                'from'=>$wallet->address,
                'to'=>$input['addressTo'],
                'amount'=>Str::remove(',',number_format($input['amount'],6))
            ];

            //send the request to query for the gas limit and gas price
            $response = $gateway->getEthGas($data);
            if ($response->ok()){
                $data = $response->json();

                $gasPriceWei =$data['gasPrice'] ;
                $gasLimit = $data['gasLimit'];
                //gas prices received are in wei so we convert to gwei
                $gasPrice = $gasPriceWei/1000000000;
                //to get the ether amount, we multiply and devide by 1G
                $fee = ($gasPrice*$gasLimit)/1000000000;

                $dataResponse = [
                    'fee'=>$fee
                ];
                return $this->sendResponse($dataResponse,'fees retrieved');
            }
            return $this->sendError('fee.error',['error'=>'Unable to estimate gas fees'],321);
        }
    }
    //top-up a particular crypto account
    public function addFunds(Request $request)
    {
        $admin = Auth::user();
        $validator = Validator::make($request->all(),[
            'amount'=>['required','numeric'],
            'pin'=>['required'],
            'id'=>['required','numeric'],
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
        //get the wallet to top up
        $wallet = Wallet::where('id',$input['id'])->first();
        if (empty($wallet)){
            return $this->sendError('wallet.error',['error'=>'Invalid wallet selected'],401);
        }
        $user = User::where('id',$wallet->user)->first();
        if (empty($user)){
            return $this->sendError('user.error',['error'=>'user account n ot found'],401);
        }

        $coin = Coin::where('asset',$wallet->asset)->first();

        if ($admin->role!=1){
            //check the role of the admin to know if they have the permission to do this.
            $role = Permission::where('id',$admin->role)->first();
            if (empty($role)){
                return $this->sendError('permission.error',
                    ['error'=>'you do not have the clearance for this'],402);
            }
            if ($role->fundUser!=1){
                return $this->sendError('permission.error',
                    ['error'=>'you do not have the clearance for this action.'],402);
            }
        }
        $data=[
            'availableBalance'=>$wallet->availableBalance+$input['amount']
        ];

        if (Wallet::where('id',$wallet->id)->update($data)){
            $mailData=[
                'amount'=>$input['amount']
            ];
            //send Notification to user
            $user->notify(new DepositMail($user,$coin,$mailData));

            $dataResponse = [
                'user'=>$user->name
            ];
            return $this->sendResponse($dataResponse,'balance top up successful');
        }
        return $this->sendError('wallet.error',['error'=>'Something went wrong'],421);
    }
    //subtract from a particular crypto account
    public function subtractFunds(Request $request)
    {
        $admin = Auth::user();
        $validator = Validator::make($request->all(),[
            'amount'=>['required','numeric'],
            'pin'=>['required'],
            'id'=>['required','numeric'],
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
        //get the wallet to top up
        $wallet = Wallet::where('id',$input['id'])->first();
        if (empty($wallet)){
            return $this->sendError('wallet.error',['error'=>'Invalid wallet selected'],401);
        }
        $user = User::where('id',$wallet->user)->first();
        if (empty($user)){
            return $this->sendError('user.error',['error'=>'user account n ot found'],401);
        }

        $coin = Coin::where('asset',$wallet->asset)->first();

        if ($admin->role!=1){
            //check the role of the admin to know if they have the permission to do this.
            $role = Permission::where('id',$admin->role)->first();
            if (empty($role)){
                return $this->sendError('permission.error',
                    ['error'=>'you do not have the clearance for this'],402);
            }
            if ($role->fundUser!=1){
                return $this->sendError('permission.error',
                    ['error'=>'you do not have the clearance for this action.'],402);
            }
        }
        $data=[
            'availableBalance'=>$wallet->availableBalance-$input['amount']
        ];

        if (Wallet::where('id',$wallet->id)->update($data)){

            $dataResponse = [
                'user'=>$user->name
            ];
            return $this->sendResponse($dataResponse,'balance debit successful');
        }
        return $this->sendError('wallet.error',['error'=>'Something went wrong'],421);
    }
}