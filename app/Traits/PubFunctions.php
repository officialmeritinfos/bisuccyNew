<?php

namespace App\Traits;

use App\Models\Coin;
use App\Models\Fiat;
use App\Models\GeneralSetting;
use App\Models\Otp;
use App\Models\SystemAccount;
use App\Models\User;
use App\Regular\Wallet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait PubFunctions
{
    public $regular;

    public function __construct()
    {
        $this->regular = new Wallet();
    }

    private static function createReference($length=10): string
    {
        return Str::random($length);
    }

    private static function createCode()
    {
        return rand(1111119,9999999);
    }
    public function generateRef($table,$column,$length=10)
    {
        $reference = $this::createReference($length);
        return DB::table($table)->where($column,$reference)->first() ?
            $this->generateRef($table,$column,$length):$reference;
    }
    public function generateToken($table,$column)
    {
        $reference = $this::createCode();
        return DB::table($table)->where($column,$reference)->first() ?
            $this->generateToken($table,$column):$reference;
    }

    public function getCryptoRate($coin,$fiat='USD')
    {
        //set the coin as the cache
        $key = strtoupper($coin);
        $values =  $this->regular->getCryptoExchange(strtoupper($coin));
        if ($fiat === null) {
            $value = $values;
        }else{
            $curr = strtoupper($fiat);
            $currencySupported = Fiat::where('code',$curr)->first();
            if ($curr == 'USD') {
                $value = $values;
            }else{
                if ($curr =='NGN'){
                    $rate = $this->regular->fetchUsdNGNRate();
                    $rateUsd = $rate['price'];
                    $rate = $rateUsd*$values;
                    $value = $rate;
                }else{
                    $rateUsd = $currencySupported->rateUsd;
                    $rate = $rateUsd*$values;
                    $value = $rate;
                }
            }
        }
        return $value;
    }
    public function getCryptoRateInstant($coin,$fiat='USD')
    {
        $settings = GeneralSetting::find(1);
        //set the coin as the cache
        $key = strtoupper($coin);
        $values= $this->regular->getCryptoExchange(strtoupper($key));
        if ($fiat === null) {
            $value = $values;
        }else{
            $curr = strtoupper($fiat);
            $currencySupported = Fiat::where('code',$curr)->first();
            if ($curr == 'USD') {
                $value = $values;
            }else{
                if ($curr =='NGN'){
                    $rate = $this->regular->fetchUsdNGNRate();
                    $rateUsd = $rate['price']-$settings->rateDiff;
                    $rate = $rateUsd*$values;
                    $value = $rate;
                }else{
                    $rateUsd = $currencySupported->rateUsd;
                    $rate = $rateUsd*$values;
                    $value = $rate;
                }
            }
        }
        return $value;
    }
    public function getRateInstant($coin,$fiat='USD')
    {
        return $this->regular->getCryptoExchange(strtoupper($coin),strtoupper($fiat));
    }
    public function getRateInstantCrypto($coin,$fiat='USD')
    {
        return $this->regular->getCryptoExchangeCrypto(strtoupper($coin),strtoupper($fiat));
    }

    public function calculateGasFeesAsset($data)
    {
        $gateway = new Wallet();

        $input = $data;

        $coin = Coin::where('asset',$input['asset'])->first();

        switch ($input['asset']){
            case 'ETH':
                $type=2;
            case 'USDT':
                $chain='ETH';
                $type=1;
                break;
            default:
                $chain='BSC';
                $type=1;
        }
        $wallet = \App\Models\Wallet::where('id',$input['walletFrom'])->first();

        if ($type==1){

            $dataM=[
                'chain'=>$chain,
                'type'=>"TRANSFER_ERC20",
                'sender'=>$wallet->address,
                'recipient'=>$input['addressTo'],
                'contractAddress'=>$coin->contractAddress,
                'amount'=>Str::remove(',',number_format($input['amount'],6))
            ];

            //send the request to query for the gas limit and gas price
            $response = $gateway->estimateGasFee($dataM);
            if ($response->ok()){
                $data = $response->json();

                $gasPrice =$data['gasPrice'] ;
                $gasLimit = $data['gasLimit'];
                //to get the ether amount, we multiply and devide by 1G
                $fee = ($gasPrice*$gasLimit)/1000000000;

                return [
                    'fee'=>$fee,
                    'gasPrice'=>number_format($gasPrice),
                    'gasLimit'=>$gasLimit
                ];
            }
        }else{
            return $this->calculateGasFeesEthAsset($data);
        }
    }
    //fetch fee for ethereum transfer from system account
    public function calculateGasFeesEthAsset($data)
    {
        $gateway = new Wallet();

        $input = $data;

        $coin = Coin::where('asset',$input['asset'])->first();

        $wallet = SystemAccount::where('id',$input['walletFrom'])->first();

        $data=[
            'from'=>$wallet->address,
            'to'=>$input['addressTo'],
            'amount'=>Str::remove(',',number_format($input['amount'],6))
        ];

        //send the request to query for the gas limit and gas price
        return $this->sendTheRequestToQueryForTheGasLimitAndGasPrice($gateway, $data);
    }
    //fetch fee for ethereum transfer from pending clearance
    public function calculateGasFeesEthAssetPendingClearance($data)
    {
        $gateway = new Wallet();

        $input = $data;

        $data=[
            'from'=>$input['addressFrom'],
            'to'=>$input['addressTo'],
            'amount'=>Str::remove(',',number_format($input['amount'],6))
        ];

        //send the request to query for the gas limit and gas price
        return $this->sendTheRequestToQueryForTheGasLimitAndGasPrice($gateway, $data);
    }

    /**
     * @param Wallet $gateway
     * @param array $data
     * @return array|void
     */
    private function sendTheRequestToQueryForTheGasLimitAndGasPrice(Wallet $gateway, array $data)
    {
        $response = $gateway->getEthGas($data);
        if ($response->ok()) {
            $data = $response->json();

            $gasPriceWei = $data['gasPrice'];
            $gasLimit = $data['gasLimit'];
            //gas prices received are in wei so we convert to gwei
            $gasPrice = $gasPriceWei / 1000000000;
            //to get the ether amount, we multiply and devide by 1G
            $fee = ($gasPrice * $gasLimit) / 1000000000;

            return [
                'fee' => $fee,
                'gasPrice' => number_format($gasPrice),
                'gasLimit' => $gasLimit
            ];
        }
    }

    //verify otp
    public function verifyOtpSent($user,$code,$purpose)
    {
        //check otp
        $otp = Otp::where(['user'=>$user->id,'purpose'=>$purpose])->first();
        if (empty($otp)){
            $error = "Something is wrong with your request. Try again";
            $stat = false;
        }elseif ($otp->codeExpires < time()){
            //check if the otp has expired
            $error = "Your OTP has timed out.";
            $stat = false;
        }else{
            $hashedOtp = Hash::check($code, $otp->token);
            if (!$hashedOtp) {

                //check if the otp is correct
                $error = "Invalid OTP token.";
                $stat = false;
            } else {
                Otp::where('user', $user->id)->delete();

                $error = "Successful";
                $stat = true;
            }
        }

        return [
            'status'=>$stat,
            'error'=>$error
        ];
    }
    //from usd to ngn
    public function fetchUsdToNgnRate()
    {
        $settings = GeneralSetting::find(1);

        $rate = $this->regular->fetchUsdNGNRate();
        $rateUsd = $rate['price']-$settings->rateDiff;
        $rate = $rateUsd;
        $value = $rate;

        return $value;
    }
    //from ngn to usd
    public function fetchNgnToUsdRate()
    {
        $settings = GeneralSetting::find(1);

        $rate = $this->regular->fetchUsdNGNRate();
        $rateUsd = $rate['price']+$settings->rateDiff;
        $rate = $rateUsd;
        $value = $rate;

        return $value;
    }
}
