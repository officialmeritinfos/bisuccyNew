<?php

namespace App\Regular;

use Illuminate\Support\Facades\Http;

class Flutterwave
{
    public mixed $pubKey,$privKey,$url;

    public function __construct()
    {
        $this->pubKey = config('constant.paystack.publicKey');
        $this->privKey = config('constant.paystack.privateKey');
        $this->url = config('constant.paystack.url');
    }
    //fetch Nigerian banks
    public function getBanks()
    {
        return Http::withToken($this->privKey)->get($this->url.'bank?country=nigeria');
    }
    //verify account number
    public function verifyAccountNumber($accountNumber,$bankCode)
    {
        return Http::withToken($this->privKey)->get($this->url.'bank/resolve?account_number='.$accountNumber.'&bank_code='.$bankCode);
    }
}
