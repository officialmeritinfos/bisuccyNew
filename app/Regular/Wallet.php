<?php
namespace App\Regular;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Wallet{

    public mixed $tetum;
    public mixed $url;
    public mixed $termiKey;
    public mixed $termiSec;
    public mixed $termiUrl;

    /**
     */
    public function __construct()
    {
        $tatumPack = config('constant.tatum.isLive');
        switch ($tatumPack){
            case 1:
                $tetum = config('constant.tatum.liveKey');
                break;
            default:
                $tetum = config('constant.tatum.testKey');
                break;
        }
        $this->tetum = $tetum;
        $this->url= config('constant.tatum.url');

        $this->termiKey = config('constant.termii.apiKey');
        $this->termiSec = config('constant.termii.secKey');
        $this->termiUrl = config('constant.termii.url');

    }
    public function generateWallet($url,$type=1): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        $accountType= ($type==1)? 'wallet':'account';
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->get($this->url.'v3/'.strtolower($url).'/'.$accountType);
    }
    public function generatePriv($url,$data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->post($this->url.'v3/'.strtolower($url).'/wallet/priv',$data);
    }
    public function createAccount($data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->post ($this->url.'v3/ledger/account',$data);
    }
    public function assignAddressToAccount($account,$address): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->post ($this->url.'v3/offchain/account/'.$account.'/address/'.$address);
    }
    public function generateAddress($account): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->post ($this->url.'v3/offchain/account/'.$account.'/address');
    }
    public function getCryptoExchange($crypto,$fiat='USD'){
//        $response = Http::withHeaders([
//            "x-api-key" =>$this->tetum
//        ])->get($this->url.'v3/tatum/rate/'.$crypto.'?basePair='.strtoupper($fiat));

        switch ($crypto){
            case 'BUSD':
            case 'USDT':
            case 'USDC':
                $coin = 'USDT';
                $skips = 1;
                break;
            default:
                $coin = $crypto.'USDT';
                $skips = 2;
                break;
        }

        if ($skips==1){
            $response =  1;
        }else{
            $responses =  Http::get('https://api.binance.com/api/v3/ticker/price?symbol='.$coin);
            $res = $responses->json();
            $response = $res['price'];
        }
        return $response;
    }
    public function getAccount($account): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->get($this->url.'v3/ledger/account/'.$account);
    }
    public function getAccountAddresses($account): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->get($this->url.'v3/offchain/account/'.$account.'/address');
    }
    public function getBalance($account): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->get($this->url.'v3/ledger/account/'.$account.'/balance');
    }
    public function createSubscription($data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->post ($this->url.'v3/subscription',$data);
    }
    public function createTransfer($url,$data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->post($this->url.'v3/offchain/'.strtolower($url).'/transfer',$data);
    }
    public function createWithdrawal($data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->post($this->url.'v3/offchain/withdrawal',$data);
    }
    public function estimateFee($data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->post($this->url.'v3/offchain/blockchain/estimate',$data);
    }
    public function sendNetworkTransaction($url,$network,$data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        /*$response = Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->post($this->url.'v3/tron/transaction',$data);*/
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->post($this->url.'v3/'.strtolower($url).'/'.strtolower($network).'/transaction',$data);
    }
    public function testEndpoint($data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->post(url('transactions/incoming/xkc3j01630966553/user/1'),$data);
    }
    public function getCryptoExchangeCrypto($coin,$crypto): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->get($this->url.'v3/tatum/rate/'.$coin.'?basePair='.strtoupper($crypto));
    }
    public function completeWithdrawal($withdrawalId, $txId): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->put($this->url.'v3/offchain/withdrawal/'.$withdrawalId.'/'.$txId);
    }
    public function cancelWithdrawal($withdrawalId): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->delete($this->url.'v3/offchain/withdrawal/'.$withdrawalId);
    }
    public function getTransactionReference($ref): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->get($this->url.'v3/ledger/transaction/reference/'.$ref);
    }
    public function getAccountTransactions($data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->post($this->url.'v3/ledger/transaction/account?pageSize=10&offset=0&count=false',$data);
    }
    public function getEthGas($data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum,
        ])->post($this->url.'v3/ethereum/gas',$data);
    }
    public function getBscGas($data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum,
        ])->post($this->url.'v3/bsc/gas',$data);
    }
    public function getEthBalance($address): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->get($this->url.'v3/ethereum/account/balance/'.$address);
    }
    public function getBscBalance($address): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->get($this->url.'v3/bsc/account/balance/'.$address);
    }
    public function getBtcBalance($address): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->get($this->url.'v3/bitcoin/address/balance/'.$address);
    }
    public function getLtcBalance($address): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->get($this->url.'v3/litecoin/address/balance/'.$address);
    }
    public function createTransaction($url,$data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->post($this->url.'v3/'.strtolower($url).'/transaction',$data);
    }
    public function estimateGasFee($data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->post($this->url.'v3/blockchain/estimate',$data);
    }
    //send token
    public function sendToken($phone)
    {
        $data = [
            'api_key'=>$this->termiKey,
            'message_type'=>'ALPHANUMERIC',
            'to'=>$phone,
            'from'=>'N-alert',
            'channel'=>'dnd',
            'pin_attempts'=>1,
            'pin_time_to_live'=>5,
            'pin_length'=>6,
            'pin_placeholder'=>"< 123456 >",
            'message_text'=>'Your Bisuccy confirmation code is < 123456 >. Code is active for 5 minutes only, one time use.'
        ];
        return Http::post($this->termiUrl.'api/sms/otp/send',$data);
    }
    //verify token
    public function verifyPhoneToken($pinId,$pin)
    {
        $data = [
            'api_key'=>$this->termiKey,
            'pin_id'=>$pinId,
            'pin'=>$pin
        ];
        return Http::post($this->termiUrl.'api/sms/otp/verify',$data);
    }
    //fetch usdt to ngn
    public function fetchUsdNGNRate()
    {
        return Http::get('https://api.binance.com/api/v3/ticker/price?symbol=USDTNGN');
    }
    public function testEndpointTat()
    {
        // return Http::get('https://api.ipify.org/?format=json');
        return Http::withHeaders([
            "x-api-key" =>$this->tetum
        ])->get('https://api.tatum.io/v3/ethereum/block/current');
    }
}
