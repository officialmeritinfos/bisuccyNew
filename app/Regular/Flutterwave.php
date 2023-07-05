<?php

namespace App\Regular;

use Illuminate\Support\Facades\Http;

class Flutterwave
{
    public mixed $pubKey,$privKey,$url;

    public function __construct()
    {
        $this->pubKey = config('constant.flutterwave.publicKey');
        $this->privKey = config('constant.flutterwave.privateKey');
        $this->url = config('constant.flutterwave.url');
    }
    //fetch Nigerian banks
    public function getBanks()
    {
        return Http::withToken($this->privKey)->get($this->url.'banks/NG');
    }
    //verify account number
    public function verifyAccountNumber($data)
    {
        return Http::withToken($this->privKey)->post($this->url.'accounts/resolve',$data);
    }
}
