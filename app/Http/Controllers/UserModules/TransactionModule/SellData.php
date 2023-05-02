<?php

namespace App\Http\Controllers\UserModules\TransactionModule;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Fiat;
use App\Models\GeneralSetting;
use App\Models\Sale;
use App\Models\User;
use App\Models\Wallet;
use App\Notifications\AdminMail;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SellData extends BaseController
{
    use PubFunctions;
    public function sellCrypto(Request $request)
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

        if($user->canSell!=1){
            return $this->sendError('validation.error',['error'=>'You cannot sell at the moment.
            Please contact support'],422);
        }
        if($web->canSell!=1){
            return $this->sendError('validation.error',['error'=>'Selling is disabled at the moment'],422);
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


        $coin = Coin::where('asset',$input['asset'])->first();
        //by default, we will assume that the sale currency is USD and convert to NGN
        $fiat = Fiat::where('code','NGN')->first();
        $balance = $user->balance;
        $rate = $this->regular->fetchUsdNGNRate();
        if (!$rate->ok()){
            return $this->sendError('rate.error',['error'=>'A conversion error just occurred. Try again']);
        }

        $rates = $rate->json();

        $sellRate = $rates['price'];
        //compute the system rate and amount
        $fiatAmount = $input['amount']*$sellRate;
        $usdRate = $this->getRateInstant($input['asset']);
        $cryptoAmount = $input['amount']/$usdRate;

        $wallet = Wallet::where(['user'=>$user->id,'asset'=>$input['asset']])->first();

        if ($wallet->availableBalance < $cryptoAmount){
            return $this->sendError('balance.error',['error'=>'Insufficient balance;
            please fund your account.'],421);
        }

        $charge = ($web->sellCharge/100)*$fiatAmount;

        $amountCredit = $fiatAmount-$charge;

        $userData=[
            'balance'=>$user->balance+$amountCredit
        ];

        $dataWallet = [
            'availableBalance'=>$wallet->availableBalance - $cryptoAmount
        ];

        $ref = $this->generateRef('purchases','reference');

        $dataSale = [
            'user'=>$user->id,'reference'=>$ref,'amount'=>$cryptoAmount,'fiatAmount'=>$input['amount'],
            'asset'=>$input['asset'],'fiat'=>'USD','rate'=>$sellRate,'charge'=>$charge,'amountCredit'=>$amountCredit,
            'rateNGN'=>$fiatAmount,'status'=>1
        ];
        $sell = Sale::create($dataSale);
        if (!empty($sell)){
            Wallet::where(['user'=>$user->id,'id'=>$wallet->id])->update($dataWallet);
            User::where('id',$user->id)->update($userData);

            $admin = User::where('isAdmin',1)->first();
            if(!empty($admin)){
                $message = "
                    A new sale of ".$coin->name." has been made on <b>".env('APP_NAME')."</b>. Find Transaction
                    details below:<br><br>
                    <b>Transaction Reference</b>:".$ref."<br><br>
                    <b>Fiat Amount</b>: $".number_format($input['amount'])."<br><br>
                    <b>Crypto Sold</b>:".$cryptoAmount.$input['asset']."<br><br>
                    <b>Naira Amount</b>:".$fiatAmount."NGN<br><br>
                    <b>Sale Rate</b>:".$sellRate." NGN<br><br>
                ";

                $admin->notify(new AdminMail($admin,$message,'New '.$coin->name.' sale'));
            }
            $dataResponse =[
                'name'=>$user->name,
                'token'=>$request->bearerToken(),
                'message'=>'Sale successful'
            ];
            return $this->sendResponse($dataResponse,'Crypto successfully sold');
        }
        return $this->sendError('purchase.error',['error'=>'Something went wrong']);
    }

    public function getUserSales($index=0)
    {
        $user = Auth::user();
        $sales = Sale::where(['user'=>$user->id])->offset($index*50)->limit(50)->get();
        return $this->getSalesData($sales);
    }
    public function getUserSalesByAsset($asset,$index=0)
    {
        $user = Auth::user();
        $sales = Sale::where(['user'=>$user->id,'asset'=>$asset])->offset($index*50)->limit(50)->get();
        return $this->getSalesData($sales);
    }


    /**
     * @param $sales
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getSalesData($sales): \Illuminate\Http\JsonResponse
    {
        if ($sales->count() < 1) {
            return $this->sendError('sales.error', ['error' => 'Nothing found']);
        }
        $dataCo = [];
        foreach ($sales as $sale) {
            $coin = Coin::where('asset', $sale->asset)->first();
            $data = [
                'amount' => $sale->amount, 'asset' => $sale->asset,
                'name' => $coin->name, 'date' => strtotime($sale->created_at),
                'fiatAmount' => $sale->amountCredit,
                'reference' => $sale->reference
            ];

            $dataCo[] = $data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
}
