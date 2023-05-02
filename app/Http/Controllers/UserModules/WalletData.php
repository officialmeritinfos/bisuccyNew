<?php

namespace App\Http\Controllers\UserModules;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Coin;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Models\Wallet;
use App\Traits\PubFunctions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletData extends BaseController
{
    use PubFunctions;
    public function getUserWallets()
    {
        $user = Auth::user();

        $wallets = Wallet::where(['user'=>$user->id,'status'=>1])->get();
        if ($wallets->count()<1){
            return $this->sendError('wallet.error',['error'=>'Nothing found.']);
        }
        $dataCo = [];
        foreach ($wallets as $wallet) {
            $data = $this->getWalletData($wallet, $user);
            $dataCo[]=$data;
        }
        return $this->sendResponse($dataCo, 'retrieved');
    }
    public function getSpecificUserWallets($asset)
    {
        $user = Auth::user();

        $wallet = Wallet::where(['user'=>$user->id,'asset'=>strtoupper($asset)])->first();
        if (empty($wallet)){
            return $this->sendError('wallet.error',['error'=>'Nothing found.']);
        }

        $data = $this->getWalletData($wallet, $user);
        return $this->sendResponse($data, 'retrieved');
    }

    /**
     * @param $wallet
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     * @return array
     */
    protected function getWalletData($wallet, ?\Illuminate\Contracts\Auth\Authenticatable $user): array
    {
        $rate = $this->getCryptoRate($wallet->asset, $user->mainCurrency);
        $coin = Coin::where('asset', $wallet->asset)->first();
        $data = [
            'asset' => $wallet->asset, 'address' => $wallet->address, 'balance' => $wallet->availableBalance,
            'memo' => ($wallet->hasMemo == 1) ? $wallet->memo : 'not applicable',
            'fiatEquivalent' => $wallet->availableBalance * $rate,
            'name' => $coin->name,
            'icon' => asset('cryptocoins/' . strtolower($coin->icon) . '.svg'),
            'usdRate' => "$rate"
        ];
        return $data;
    }
}
