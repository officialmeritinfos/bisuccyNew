<?php

namespace App\Console\Commands;

use App\Models\SignalPackage;
use App\Models\User;
use App\Notifications\AdminMail;
use Illuminate\Console\Command;

class CheckUserSubscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:userSubscription';

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
        if ($users->count()>0){
            foreach ($users as $user) {
                //we will cancel the subscription
                $dataUser = [
                    'enrolledInSignal'=>2
                ];
                if (User::where('id',$user->id)->update($dataUser)){
                    //get his enrolled signal
                    $signal = SignalPackage::where('id',$user->packageEnrolled)->first();
                    //send a mail to user
                    $messageToUser ="
                        Your subscription to the signal ".$signal->name." package on ".env('APP_NAME')."
                        has expired and your access into the signal room restricted. <br>
                        To continue enjoying our signal features, please resubscribe to the package.
                    ";
                    $messageToAdmin = "
                        The signal subscription of the user ".$user->name." on ".env('APP_NAME')." has
                        expired and his access to the signal room restricted.
                    ";
                    $user->notify(new AdminMail($user,$messageToUser,'Signal subscription cancelled'));

                    //check if admin exists
                    $admin = User::where(['isAdmin'=>1,'role'=>1])->first();
                    if (!empty($admin)){
                        $admin->notify(new AdminMail($admin,$messageToAdmin,'Signal Subscription expiration'));
                    }
                }
            }
        }
    }
}
