<?php

namespace App\Http\Controllers\UserModules;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Fiat;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BalanceData extends BaseController
{
    use PubFunctions;
    //user crypto balance
    public function getUserCryptoBalance($asset)
    {
        $user = Auth::user();

        $coin = Coin::where('asset',strtoupper($asset))->first();
        if (empty($coin)){
            return $this->sendError('coin.error',['error'=>'Asset Not supported']);
        }
        $wallet = \App\Models\Wallet::where(['user'=>$user->id,'asset'=>strtoupper($asset)])->first();

        $dataResponse=[
            'balance'=>$wallet->availableBalance
        ];
        return $this->sendResponse($dataResponse,'retrieved');
    }
    //user fiat balance
    public function getUserFiatBalance()
    {
        $user = Auth::user();
        $currency = Fiat::where('code',strtoupper($user->mainCurrency))->first();
        if (empty($currency)){
            return $this->sendError('fiat.error',['error'=>'Fiat Not supported']);
        }
        $dataResponse=[
            'balance'=>$user->balance*$currency->rateNGN,
            'usdRate'=>$currency->rateUsd,
            'sellRate'=>$currency->sellRate,
            'buyRate'=>$currency->buyRate,
            'currency'=>$user->mainCurrency,
            'NgnBalance'=>$user->balance
        ];
        return $this->sendResponse($dataResponse,'retrieved');
    }
}
