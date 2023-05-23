<?php

namespace App\Console\Commands;

use App\Models\Coin;
use App\Models\User;
use App\Models\Wallet;
use App\Regular\OneLiquidity;
use App\Traits\PubFunctions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateUserWallet extends Command
{
    use PubFunctions;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:userWallet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates user crypto Wallet';

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function __construct() {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $oneLiquidity = new OneLiquidity();
            $users = User::where('status',1)->get();
            if ($users->count()>0){
                foreach ($users as $user) {
                    $coins = Coin::where(['status'=>1,'hasTest'=>1])->get();
                    if ($coins->count()>0){
                        foreach ($coins as $coin) {
                            $wallet = Wallet::where(['user'=>$user->id,'asset'=>$coin->asset])->first();
                            if (empty($wallet)){
                                $customId = $this->generateRef('wallets','customId','20');
                                $data =[
                                    'currency'=>$coin->asset,
                                    'uid'=>$customId
                                ];
                                $response = $oneLiquidity->generateSubWallet($data);
                                if ($response->successful()){
                                    $data = json_decode($response,1);
                                    $walletId = $data['data']['walletId'];
                                    $addresses = $data['data']['addresses'];
                                    foreach ($addresses as $address) {
                                        Wallet::create([
                                            'asset'=>$coin->asset,'address'=>$address['address'],
                                            'network'=>$address['network'],'walletId'=>$walletId,
                                            'customId'=>$customId,'user'=>$user->id
                                        ]);
                                    }
                                }else{
                                    Log::info('Error: '.$response);
                                }
                            }
                        }
                    }
                }
            }
        }catch (\Exception $exception){
            Log::info('Error while Generating User wallet.\n'.$exception);
        }
    }
}
