<?php

namespace App\Http\Controllers\UserModules\TransactionModule;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\GeneralSetting;
use App\Models\Swap;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Notifications\AdminMail;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SwapData extends BaseController
{
    use PubFunctions;
    public function processSwap(Request $request)
    {
        $user = Auth::user();
        $web= GeneralSetting::find(1);
        $validator = Validator::make($request->all(), [
            'amount' => ['required', 'numeric'],
            'password'=>['required','current_password:api'],
            'from' => ['required', 'string','exists:coins,asset'],
            'to' => ['required', 'string','exists:coins,asset'],
            'rate' => ['nullable', 'numeric'],
            'code'=>['required','numeric'],
            'purpose'=>['required','string']
        ])->stopOnFirstFailure();
        if ($validator->fails()) {
            return $this->sendError('validation.error', ['error' => $validator->errors()->all()], 422);
        }
        $input = $validator->validated();

        //check otp
        $stat = $this->verifyOtpSent($user,$input['code'],$input['purpose']);
        if (!$stat['status']){
            return $this->sendError('otp.error',['error'=>$stat['error']]);
        }

        $coin = Coin::where('asset',$input['from'])->first();
        //get the coin being converted to
        $coinTo = Coin::where('asset',$input['to'])->first();
        //check if the user has enough balance
        $wallet = UserWallet::where(['user'=>$user->id,'asset'=>$input['from']])->first();
        $walletTo = UserWallet::where(['user'=>$user->id,'asset'=>$input['to']])->first();

        $availableBalance = $wallet->floatBalance;

        if ($availableBalance < $input['amount']){
            return $this->sendError('balance.error',['error'=>'Insufficient balance. Please topup.']);
        }
        //check if the amount to swap is up to the minimum swap allowed
        if ($input['amount'] < $coin->minSwap){
            return $this->sendError('swap.error',['error'=>'Minimum amount to swap is '.$coin->minSwap]);
        }
        //get conversion
        if (!empty($input['rate'])){
            $rate = $input['rate'];
        }else{
            //first convert base to usd
            $firstUsdRate = $this->getRateInstant(strtoupper($input['from']));
            //then get the usd rate of the second coin
            //we perform some arithmetic calculations
            $secondUsdRate =  $this->getRateInstant(strtoupper($input['to']));

            $rate = $secondUsdRate/$firstUsdRate;
        }
        $amountTo = $input['amount']/$rate;
        $charge = ($coinTo->swapCharge/100)*$amountTo;

        $amountToCredit = $amountTo - $charge;
        $ref = $this->generateRef('swaps','reference');

        $dataSwap = [
            'user'=>$user->id,'assetFrom'=>$input['from'],'assetTo'=>$input['to'],
            'amountFrom'=>$input['amount'],'amountTo'=>$amountTo,'charge'=>$charge,
            'amountCredit'=>$amountToCredit,
            'status'=>1,
            'reference'=>$ref
        ];
        $dataBalance = [
            'floatBalance'=>$walletTo->floatBalance+$amountToCredit
        ];
        $dataBalanceFrom = [
            'floatBalance'=>$wallet->floatBalance-$input['amount']
        ];

        $swap = Swap::create($dataSwap);
        if (!empty($swap)){
            UserWallet::where('id',$wallet->id)->update($dataBalanceFrom);
            UserWallet::where('id',$walletTo->id)->update($dataBalance);

            //send email to admin
            $admin = User::where('isAdmin',1)->first();
            if(!empty($admin)){
                $message = "
                    A new swap of ".$coin->name." has been made on <b>".env('APP_NAME')."</b>. Find Transaction
                    details below:<br><br>
                    <b>Transaction Reference</b>:".$ref."<br><br>
                    <b>Amount From </b>:".$input['amount'].' '.$input['from']."<br><br>
                    <b>Swap Received</b>:".$amountTo.' '.$coinTo->asset."<br><br>
                    <b>Amount Credited</b>:".$amountToCredit.' '.$coinTo->asset."<br><br>
                    <b>Charge</b>:".$charge.$input['to']." <br><br>
                ";
                $admin->notify(new AdminMail($admin,$message,'New '.$coin->name.' Swap'));
            }
            $dataResponse = [
                'name'=>$user->name,
                'token'=>$request->bearerToken(),
            ];
            return $this->sendResponse($dataResponse,'swap successful');
        }
        return $this->sendError('swap.error',['error'=>'something went wrong']);
    }
    //fetch all user swapping
    public function getUserSwapList()
    {
        $user = Auth::user();

        $swaps = Swap::where('user',$user->id)->get();
        if ($swaps->count()<1){
            return $this->sendError('swap.error',['error'=>'no data found']);
        }
        $dataCo =[];

        foreach ($swaps as $swap) {
            $coinTo = Coin::where('asset',$swap->assetTo)->first();
            $coinFrom = Coin::where('asset',$swap->assetFrom)->first();

            $data=[
                'reference'=>$swap->reference,
                'amountCredited'=>$swap->amountCredit,
                'amountSwapped'=>$swap->amountFrom,
                'swapEquivalent'=>$swap->amountTo,
                'charge'=>$swap->charge,
                'status'=>($swap->status ==1)?'completed':'pending',
                'assetTo'=>$swap->assetTo,
                'assetFrom'=>$swap->assetFrom,
                'dateInitiated'=>$swap->created_at
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo,'retrieved');
    }

}
