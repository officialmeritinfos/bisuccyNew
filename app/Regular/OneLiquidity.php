<?php

namespace App\Regular;

use Illuminate\Support\Facades\Http;

class OneLiquidity
{
    public mixed $url, $key;

    public function __construct()
    {
        switch (config('constant.oneLiquidity.isLive')){
            case 1:
                $this->url = config('constant.oneLiquidity.liveUrl');
                break;
            default:
                $this->url = config('constant.oneLiquidity.testUrl');
        }
        $this->key = config('constant.oneLiquidity.token');
    }
    //register integrator
    public function registerIntegrator($data): \Illuminate\Http\Client\Response
    {
        return Http::post($this->url.'integrator/v1/register',$data);
    }
    //generate authentication token
    public function generateToken($data): \Illuminate\Http\Client\Response
    {
        return Http::post($this->url.'auth/v1/token',$data);
    }
    //create ledger account
    public function createFloatLedger($data): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
        return Http::withToken($this->key)->post($this->url.'integrator/v1/float',$data);
    }
    //initiate deposit
    public function initiateDeposit($data)
    {
        return Http::withToken($this->key)->post($this->url.'integrator/v1/deposit/float',$data);
    }
    //fetch deposit
    public function fetchDeposit($id)
    {
        return Http::withToken($this->key)->get($this->url.'integrator/v1/deposit/float?deposit='.$id);
    }
    //generate main wallet
    public function generateMainWallet($data)
    {
        return Http::withToken($this->key)->post($this->url.'wallets/v1/main',$data);
    }
    //generate sub wallet
    public function generateSubWallet($data)
    {
        return Http::withToken($this->key)->post($this->url.'wallets/v1/sub',$data);
    }
}
