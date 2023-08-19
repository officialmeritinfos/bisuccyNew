<?php

namespace App\Console\Commands;

use App\Models\Coin;
use App\Models\SystemAccount;
use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InitializeUserWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'initialize:userWallet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializes user main wallet';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::where('status',1)->get();
        if ($users->count()>0){
            foreach ($users as $user) {
                $coins = Coin::where(['status'=>1,'hasTest'=>1])->get();
                if ($coins->count()>0){
                    foreach ($coins as $coin) {
                        //get system account
                        $userAccount = UserWallet::where('asset',$coin->asset)->where('user',$user->id)->first();
                        if (empty($userAccount)){
                            UserWallet::create([
                                'user'=>$user->id,
                                'asset'=>$coin->asset
                            ]);
                        }
                    }
                }
            }
        }
    }
}
