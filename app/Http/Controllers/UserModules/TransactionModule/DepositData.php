<?php

namespace App\Http\Controllers\UserModules\TransactionModule;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Deposit;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositData extends BaseController
{
    use PubFunctions;
    //return user's deposits
    public function getDeposits($index=0)
    {
        $user = Auth::user();
        $deposits = Deposit::where(['user'=>$user->id])->offset($index*50)->limit(50)->get();

        $dataCo=[];
        foreach ($deposits as $deposit) {
            $coin = Coin::where('asset',$deposit->asset)->first();
            $rate = $this->getCryptoRate($coin->asset);
            $data=[
                'amount'=>$deposit->amount,'asset'=>$deposit->asset,
                'name'=>$coin->name,'date'=>strtotime($deposit->created_at),
                'fiatEquivalent'=>$deposit->amount*$rate,'txId'=>$deposit->transHash
            ];

            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    //filter the deposits by asset
    public function getDepositByAsset($asset,$index=0)
    {
        $user = Auth::user();
        $deposits = Deposit::where(['user'=>$user->id,'asset'=>$asset])->offset($index*50)->limit(50)->get();
        $dataCo=[];
        foreach ($deposits as $deposit) {
            $coin = Coin::where('asset',$deposit->asset)->first();
            $rate = $this->getCryptoRate($coin->asset);
            $data=[
                'amount'=>$deposit->amount,'asset'=>$deposit->asset,
                'name'=>$coin->name,'date'=>strtotime($deposit->created_at),
                'fiatEquivalent'=>$deposit->amount*$rate,
                'txId'=>$deposit->transHash
            ];

            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
}
