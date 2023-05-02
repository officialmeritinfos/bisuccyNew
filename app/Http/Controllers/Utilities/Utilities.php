<?php

namespace App\Http\Controllers\Utilities;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Country;
use App\Models\Faq;
use App\Models\Fiat;
use App\Models\GeneralSetting;
use App\Models\SignalPackage;
use App\Models\SignalPackageFeature;
use App\Models\User;
use App\Regular\Wallet;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Utilities extends BaseController
{
    use PubFunctions;
    public $regular;
    public function __construct()
    {
        $this->regular = new Wallet();
    }
    public function fetchCountries()
    {
        $countries = Country::where('status',1)->orderBy('name','asc')->get();
        if ($countries->count()<1){
            return $this->sendError('country.error',['error'=>'Nothing found.']);
        }
        $dataCo = [];
        foreach ($countries as $country) {
            $data=[
                'name'=>$country->name,'phoneCode'=>$country->phonecode,
                'currency'=>$country->currency,'currencySign'=>$country->currency_symbol,
                'countryCode'=>$country->iso3
            ];
            $dataCo[] = $data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    public function getCurrencies()
    {
        $currencies = Fiat::where('status',1)->orderBy('name','asc')->get();
        if ($currencies->count()<1){
            return $this->sendError('currency.error',['error'=>'Nothing found.']);
        }
        $dataCo = [];
        foreach ($currencies as $currency) {
            $data=[
                'name'=>$currency->name,'code'=>$currency->code,
                'nairaRate'=>$currency->rateNGN,'usdRate'=>$currency->rateUsd,
                'withdrawalFee'=>$currency->withdrawalFee,'limitForVerifiedUsers'=>$currency->verifiedLimit,
                'limitForUnverifiedUser'=>$currency->unverifiedLimit,'sign'=>$currency->sign,

            ];
            $dataCo[] = $data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function getFaq()
    {
        $faqs = Faq::where('status',1)->get();
        if ($faqs->count()<1){
            return $this->sendError('faq.error',['error'=>'Nothing found.']);
        }
        $dataCo = [];

        foreach ($faqs as $faq) {
            $data=[
                'question'=>$faq->question,'answer'=>$faq->answer
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function getContact()
    {
        $web = GeneralSetting::find(1);

        $dataResponse=[
            'phone'=>$web->phone,'email'=>$web->email
        ];
        return $this->sendResponse($dataResponse,'retrieved');
    }
    public function getSupportedTokens($fiat='USD')
    {
        $coins = Coin::where('status',1)->get();
        if ($coins->count()<1){
            return $this->sendError('token.error',['error'=>'Nothing found.']);
        }
        $dataCo = [];

        foreach ($coins as $coin) {
            $rate = $this->getCryptoRate($coin->asset,$fiat);

            $rates =number_format($rate,8);

            $rate = str_replace(',','',$rates);
            $data=[
                'name'=>$coin->name,'asset'=>$coin->asset,
                'icon'=>asset('cryptocoins/'.strtolower($coin->icon).'.svg'),
                'usdRate'=>"$rate"
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    public function getSendingFee($asset)
    {
        $coin = Coin::where(['asset'=>strtoupper($asset),'status'=>1])->first();
        $rate = $this->getCryptoRate($coin->asset);
        switch ($coin->asset){
            case 'BNB':
            case 'BCH':
            case 'LTC':
            case 'ETH':
            case 'BTC':
                $dataResponse=[
                    'fee'=>[
                        'data'=>[
                            'value'=>$coin->networkFee,
                            'usdRate'=>$coin->networkFee*$rate
                        ]
                    ]
                ];
                break;

        }
        return $this->sendResponse($dataResponse,'retrieved');
    }
    public function getRecipientDetails($email)
    {
        $user = User::where('email',$email)->first();
        if (empty($user)){
            return $this->sendError('recipient.error',['error'=>'Nothing found.']);
        }
        $dataResponse=[
            'name'=>$user->name,
            'ref'=>$user->userRef,
            'email'=>$user->email
        ];
        return $this->sendResponse($dataResponse,'retrieved');
    }
    public function getRecipientDetailsPhone($phone)
    {
        $user = User::where('phone',$phone)->first();
        if (empty($user)){
            return $this->sendError('recipient.error',['error'=>'Nothing found.']);
        }
        $dataResponse=[
            'name'=>$user->name,
            'ref'=>$user->userRef,
            'phone'=>$user->phone
        ];
        return $this->sendResponse($dataResponse,'retrieved');
    }
    public function getRateCryptoNow($asset,$fiat='USD')
    {
        $rate = $this->getRateInstant($asset,'USD');
        $currency = Fiat::where('code',$fiat)->first();
        if (empty($currency)){
            return $this->sendError('fiat.error',['error'=>'Fiat Not supported']);
        }
        $rates = number_format($rate,8);
        $rate = str_replace(',','',$rates);
        $dataResponse=[
            'value'=>$rate*$currency->rateUsd,
            'usdRate'=>$currency->rateUsd,
            'usdValue'=>$rate
        ];
        return $this->sendResponse($dataResponse,'retrieved');
    }


    public function getFiatToCryptoRate($fiat,$asset)
    {
        $currency = Fiat::where('code',$fiat)->first();
        $coin = Coin::where('asset',$asset)->first();
        if (empty($currency)){
            return $this->sendError('fiat.error',['error'=>'Fiat Not supported']);
        }
        if (empty($coin)){
            return $this->sendError('crypto.error',['error'=>'Asset Not supported']);
        }

        $rate = $this->getRateInstant($asset,$fiat);

        $rates = number_format($rate,8);
        $rate = str_replace(',','',$rates);

        $dataResponse=[
            'value'=>$rate*$currency->rateUsd,
            'usdRate'=>$currency->rateUsd,
            'usdValue'=>$rate
        ];
        return $this->sendResponse($dataResponse,'retrieved');
    }

    public function getCryptoToCryptoRate($base,$to,$amount=1)
    {
        $coin = Coin::where('asset',$base)->first();
        $coinTo = Coin::where('asset',$to)->first();
        if (empty($coin)){
            return $this->sendError('crypto.error',['error'=>'Base asset Not supported']);
        }
        if (empty($coinTo)){
            return $this->sendError('crypto.error',['error'=>'Pair asset Not supported']);
        }
        //first convert base to usd
        $firstUsdRate = $this->getRateInstant(strtoupper($base));
        //then get the usd rate of the second coin
        //we perform some arithmetic calculations
        $secondUsdRate =  $this->getRateInstant(strtoupper($to));
        if ($firstUsdRate <0){
            return $this->sendError('crypto.error',['error'=>'Something went wrong while retrieving rate']);
        }
        $rate = $firstUsdRate/$secondUsdRate;

        $amtRate = $rate*$amount;
        $dataResponse=[
            'value'=>"$rate",
            'rate'=>"$amtRate",
            'asset'=>$to
        ];
        return $this->sendResponse($dataResponse,'retrieved');
    }

    public function fetchSignalPackages()
    {
        $packages = SignalPackage::where('status',1)->get();
        if ($packages->count()<1){
            return $this->sendError('signal.error',['error'=>'No package found']);
        }
        $dataCo=[];

        foreach ($packages as $package) {
            $features = SignalPackageFeature::where('packageId',$package->id)->get();
            $dataPack=[];
            if ($features->count()>0){
                foreach ($features as $feature) {
                    $dataP=[
                        'feature'=>$feature->content
                    ];
                    $dataPack[]=$dataP;
                }
            }
            $data=[
                'id'=>$package->id,
                'name'=>$package->name,
                'amount'=>$package->amount,
                'duration'=>$package->duration,
                'interval'=>$package->interval,
                'status'=>($package->status==1)?'active':'inactive',
                'features'=>$dataPack
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo,'retrieved');
    }
    //convert from usd to crypto
    public function getUsdToCryptoRate(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'coin'=>['required','string'],
            'amount'=>['required','numeric'],
        ])->stopOnFirstFailure();
        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        //fetch the rate and convert it
        $input = $validator->validated();

        $rate = $this->getRateInstant($input['coin']);
        if ($rate <0){
            return $this->sendError('crypto.error',[
                'error'=>'Something went wrong while retrieving rate.']);
        }
        $returnRate = $input['amount']/$rate;

        $rates = number_format($returnRate,8);
        $returnRate = str_replace(',','',$rates);

        $dataReturn = [
            'coinRate'=>$returnRate,
            'coin'=>$input['coin']
        ];
        return $this->sendResponse($dataReturn,'rate retrieved');
    }
    //convert from ngn to crypto
    public function getNGNToCrypto(Request $request)
    {
        $settings = GeneralSetting::find(1);

        $validator = Validator::make($request->all(),[
            'coin'=>['required','string'],
            'amount'=>['required','numeric'],
        ])->stopOnFirstFailure();
        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        //fetch the rate and convert it
        $input = $validator->validated();

        $rate = $this->getCryptoRateInstant($input['coin'],'NGN');
        if ($rate <0){
            return $this->sendError('crypto.error',[
                'error'=>'Something went wrong while retrieving rate.']);
        }
        $returnRate = $input['amount']/$rate;

        $rates = number_format($returnRate,8);
        $returnRate = str_replace(',','',$rates);

        $dataReturn = [
            'coinRate'=>$returnRate,
            'coin'=>$input['coin'],
            'fiat'=>'NGN'
        ];
        return $this->sendResponse($dataReturn,'rate retrieved');
    }
    //convert from crypto to usd
    public function convertFromCryptoToUsd(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'coin'=>['required','string'],
            'amount'=>['required','numeric'],
        ])->stopOnFirstFailure();
        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        //fetch the rate and convert it
        $input = $validator->validated();

        $rate = $this->getRateInstant($input['coin']);
        if ($rate <0){
            return $this->sendError('crypto.error',[
                'error'=>'Something went wrong while retrieving rate.']);
        }
        $returnRate = $input['amount']*$rate;

        $rates = number_format($returnRate,8);
        $returnRate = str_replace(',','',$rates);


        $dataReturn = [
            'coinRate'=>$returnRate,
            'coin'=>$input['coin']
        ];
        return $this->sendResponse($dataReturn,'rate retrieved');
    }
    public function convertFromCryptoToNgn(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'coin'=>['required','string'],
            'amount'=>['required','numeric'],
        ])->stopOnFirstFailure();
        if ($validator->fails()){
            return $this->sendError('validation.error',['error'=>$validator->errors()->all()],422);
        }
        //fetch the rate and convert it
        $input = $validator->validated();

        $rate = $this->getCryptoRateInstant($input['coin'],'NGN');
        if ($rate <0){
            return $this->sendError('crypto.error',[
                'error'=>'Something went wrong while retrieving rate.']);
        }
        $returnRate = $input['amount']*$rate;

        $rates = number_format($returnRate,8);
        $returnRate = str_replace(',','',$rates);

        $dataReturn = [
            'coinRate'=>$returnRate,
            'coin'=>$input['coin'],
            'fiat'=>'NGN'
        ];
        return $this->sendResponse($dataReturn,'rate retrieved');
    }
    public function convertUsdToNgn($amount=1)
    {
        $settings = GeneralSetting::find(1);

        $rate = $this->regular->fetchUsdNGNRate();
        if ($rate->ok()){
            $rates = $rate->json();

            $mainRates = number_format(($rates['price']-$settings->rateDiff)*$amount,8);

            $mainRate = str_replace(',','',$mainRates);

            $dataReturn=[
                'value'=>$rates['price'],
                'rate'=>$mainRate,
                'currency'=>'NGN'
            ];
            return $this->sendResponse($dataReturn,'rate retrieved');
        }
        return $this->sendError('conversion.error',['error'=>'Unable to retrieve rate']);
    }
}
