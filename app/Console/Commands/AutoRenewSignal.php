<?php

namespace App\Console\Commands;

use App\Models\SignalPackage;
use App\Models\User;
use Illuminate\Console\Command;

class AutoRenewSignal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $time = time();
        //fetch users that their package has expired
        $users = User::where('timeRenewPayment','<=',$time)->where('enrolledInSignal',1)->get();
        if ($users->count()>0) {
            foreach ($users as $user) {
                $signal = SignalPackage::where('id',$user->packageEnrolled)->first();
                //check if the user balance is enough
                $accountBalance = $user->balance;
                //get the amount needed for conversion
                $packageAmount = $signal->amount;
                //get the current rate

            }
        }
    }
}
