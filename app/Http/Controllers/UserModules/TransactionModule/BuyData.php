<?php

namespace App\Http\Controllers\UserModules\TransactionModule;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Fiat;
use App\Models\GeneralSetting;
use App\Models\Purchase;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Notifications\AdminMail;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BuyData extends BaseController
{

    use PubFunctions;
    public function buyCrypto(Request $request)
    {
        $user = Auth::user();
        $web = GeneralSetting::find(1);
        $validator = Validator::make($request->all(),[
            'asset'=>['required','alpha_dash'],
            'amount'=>['required','numeric'],
            'password'=>['required','current_password:api'],
            'code'=>['required','numeric'],
            'purpose'=>['required','string']
        ])->stopOnFirstFailure();

        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }

        if($user->canBuy!=1){
            return $this->sendError('validation.error',['error'=>'You cannot buy at the moment.
            Please contact support'],422);
        }
        if($web->canBuy!=1){
            return $this->sendError('validation.error',['error'=>'Buying is disabled at the moment'],422);
        }
        if($web->maintenance==1){
            return $this->sendError('validation.error',['error'=>'Maintenance is currently active. Try again later.'],
                422);
        }
        $input=$validator->validated();


        //check otp
        $stat = $this->verifyOtpSent($user,$input['code'],$input['purpose']);
        if (!$stat['status']){
            return $this->sendError('otp.error',['error'=>$stat['error']]);
        }

        //by default, we will assume that the buying currency is USD and convert to NGN
        $fiat = Fiat::where('code','NGN')->first();

        $balance = $user->balance;
//
//        $rate = $this->regular->fetchUsdNGNRate();
//        if (!$rate->ok()){
//            return $this->sendError('rate.error',['error'=>'A conversion error just occurred. Try again']);
//        }
//
//        $rates = $rate->json();

        //$buyRate =$rates['price'];
        //compute the system rate and amount
        $amountNeededInBalance = $input['amount'];
        $usdRate = $this->getRateInstant($input['asset']);
        $cryptoAmount = $input['amount']/$usdRate;
        //check if user has enough balance to cover for the purchase
        if ($balance < $amountNeededInBalance){
            return $this->sendError('balance.error',['error'=>'Insufficient balance;
            please fund your account.'],421);
        }
        $coin = Coin::where('asset',$input['asset'])->first();
        $wallet = UserWallet::where(['user'=>$user->id,'asset'=>$input['asset']])->first();


        $charge = ($web->buyCharge/100)*$cryptoAmount;

        $amountCredit = $cryptoAmount-$charge;

        $dataUser = [
            'balance'=>$user->balance - $amountNeededInBalance
        ];

        $dataWallet = [
            'floatBalance'=>$wallet->floatBalance + $amountCredit
        ];

        $ref = $this->generateRef('purchases','reference');

        $dataPurchase = [
            'user'=>$user->id,'reference'=>$ref,'amount'=>$cryptoAmount,'fiatAmount'=>$input['amount'],
            'asset'=>$input['asset'],'fiat'=>'USD','rate'=>$usdRate,'charge'=>$charge,'amountCredit'=>$amountCredit,
            'rateNGN'=>$input['amount'],'status'=>1
        ];

        $buy = Purchase::create($dataPurchase);
        if (!empty($buy)){
            UserWallet::where(['user'=>$user->id,'id'=>$wallet->id])->update($dataWallet);
            User::where('id',$user->id)->update($dataUser);
            //send a mail to the user
            $userMessage = "
                    A new purchase of ".$coin->name." has been made on your <b>".env('APP_NAME')."</b> account.
                    Find Transaction details below:<br><br>
                    <b>Transaction Reference</b>:".$ref."<br><br>
                    <b>Fiat Amount</b>: $".number_format($input['amount'])."<br><br>
                    <b>Crypto Credited</b>:".$amountCredit.$input['asset']."<br><br>
                    <b>Buy Rate</b>:".$usdRate." USD<br><br>
                ";

            $user->notify(new AdminMail($user,$userMessage,'New '.$coin->name.' purchase'));

            $admin = User::where('isAdmin',1)->first();
            if(!empty($admin)){
                $message = "
                    A new purchase of ".$coin->name." has been made on <b>".env('APP_NAME')."</b>. Find Transaction
                    details below:<br><br>
                    <b>Transaction Reference</b>:".$ref."<br><br>
                    <b>Fiat Amount</b>: $".number_format($input['amount'])."<br><br>
                    <b>Crypto Credited</b>:".$amountCredit.$input['asset']."<br><br>
                    <b>Buy Rate</b>:".$usdRate." USD<br><br>
                ";

                $admin->notify(new AdminMail($admin,$message,'New '.$coin->name.' purchase'));
            }
            $dataResponse =[
                'name'=>$user->name,
                'token'=>$request->bearerToken(),
                'message'=>'Buy successful'
            ];
            return $this->sendResponse($dataResponse,'Crypto successfully purchased');
        }
        return $this->sendError('purchase.error',['error'=>'Something went wrong']);
    }
    public function getUserPurchases($index=0)
    {
        $user = Auth::user();
        $purchases = Purchase::where(['user'=>$user->id])->offset($index*50)->limit(50)->get();
        return $this->getPurchaseData($purchases);
    }
    public function getUserPurchasesAsset($asset,$index=0)
    {
        $user = Auth::user();
        $purchases = Purchase::where(['user'=>$user->id,'asset'=>$asset])->offset($index*50)->limit(50)->get();
        return $this->getPurchaseData($purchases);
    }

    /**
     * @param $purchases
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getPurchaseData($purchases): \Illuminate\Http\JsonResponse
    {
        if ($purchases->count() < 1) {
            return $this->sendError('purchase.error', ['error' => 'Nothing found']);
        }
        $dataCo = [];
        foreach ($purchases as $purchase) {
            $coin = Coin::where('asset', $purchase->asset)->first();
            $data = [
                'amount' => $purchase->amount, 'asset' => $purchase->asset,
                'name' => $coin->name, 'date' => strtotime($purchase->created_at),
                'fiatAmount' => $purchase->fiatAmount,
                'reference' => $purchase->reference,
                'rate'=>$purchase->rate,'fiat'=>$purchase->fiat,
                'amountReceived'=>$purchase->amountCredit
            ];

            $dataCo[] = $data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
}
