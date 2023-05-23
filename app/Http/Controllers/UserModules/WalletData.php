<?php

namespace App\Http\Controllers\UserModules;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\CoinNetwork;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletData extends BaseController
{
    use PubFunctions;
//    public function getUserWallets()
//    {
//        $user = Auth::user();
//
//        $wallets = Wallet::where(['user'=>$user->id,'status'=>1])->get();
//        return $this->getWalletBasedOnNetwork($wallets, $user);
//    }
    public function getUserWallets()
    {
        $user = Auth::user();

        $userWallets = UserWallet::where(['user'=>$user->id])->get();
        if ($userWallets->count() < 1) {
            return $this->sendError('wallet.error', ['error' => 'Nothing found.']);
        }
        $dataCo = [];
        foreach ($userWallets as $userWallet) {
            $coin = Coin::where('asset',$userWallet->asset)->first();
            $rate = $this->getCryptoRate($userWallet->asset, $user->mainCurrency);
            $networks = Wallet::where(['user'=>$user->id,'asset'=>$userWallet->asset])->get();
            $dataNetCo =[];
            if ($networks->count()>0){
                foreach ($networks as $network) {
                    $dataNet = [
                        'network'=>$network->network,
                        'address'=>$network->address
                    ];
                    $dataNetCo[]=$dataNet;
                }
            }
            $data = [
                'currency'=>$userWallet->asset,
                'addresses'=>$dataNetCo,
                'fiatEquivalent' => $userWallet->availableBalance * $rate,
                'name' => $coin->name,
                'icon' => asset('cryptocoins/' . strtolower($coin->icon) . '.svg'),
                'usdRate' => "$rate",
            ];
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }

    public function getSpecificUserWallets($asset)
    {
        $user = Auth::user();

        $userWallet = UserWallet::where(['user'=>$user->id,'asset'=>$asset])->first();
        if (empty($userWallet)) {
            return $this->sendError('wallet.error', ['error' => 'Nothing found.']);
        }
        $dataCo = [];
        $coin = Coin::where('asset',$userWallet->asset)->first();
        $rate = $this->getCryptoRate($userWallet->asset, $user->mainCurrency);
        $networks = Wallet::where(['user'=>$user->id,'asset'=>$userWallet->asset])->get();
        $dataNetCo =[];
        if ($networks->count()>0){
            foreach ($networks as $network) {
                $dataNet = [
                    'network'=>$network->network,
                    'address'=>$network->address
                ];
                $dataNetCo[]=$dataNet;
            }
        }
        $data = [
            'currency'=>$userWallet->asset,
            'addresses'=>$dataNetCo,
            'fiatEquivalent' => $userWallet->availableBalance * $rate,
            'name' => $coin->name,
            'icon' => asset('cryptocoins/' . strtolower($coin->icon) . '.svg'),
            'usdRate' => "$rate",
        ];
        $dataCo[]=$data;
        return $this->sendResponse($dataCo, 'retrieved');
    }

}
