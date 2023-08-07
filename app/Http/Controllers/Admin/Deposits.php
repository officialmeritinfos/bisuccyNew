<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\Deposit;
use App\Models\User;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;

class Deposits extends BaseController
{
    use PubFunctions;

    public function landingPage()
    {
        return view('deposits.index');
    }

    public function getDeposits($index=0)
    {
        $deposits = Deposit::where('status',1)->offset($index*50)->limit(50)->get();
        $dataCo=[];
        foreach ($deposits as $deposit) {
            $coin = Coin::where('asset',$deposit->asset)->first();
            $rate = $this->getCryptoRate($coin->asset);
            $user = User::where('id',$deposit->user)->first();
            $data=[
                'amount'=>$deposit->amount,'asset'=>$deposit->asset,
                'name'=>$coin->name,'date'=>strtotime($deposit->created_at),
                'fiatEquivalent'=>$deposit->amount*$rate,'txId'=>$deposit->transHash,
                'memo'=>$deposit->memo,'user'=>$user->name,
                'network'=>$deposit->network,
                'depositId'=>$deposit->depositId
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
}
