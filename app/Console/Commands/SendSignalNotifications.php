<?php

namespace App\Console\Commands;

use App\Models\SignalNotification;
use App\Models\SignalPackage;
use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Console\Command;

class SendSignalNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:signalNotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command sends notification when a new signal is published';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $moment = time();
        $signals = SignalNotification::where('timeToBroadcast','<=',$moment)->where('broadCasted',2)->get();
        if ($signals->count()>0){
            foreach ($signals as $signal) {
                //get the package
                if ($signal->package =='all'){
                    $this->sendNotificationToAllSubscribers($signal);
                }else{
                    $package = SignalPackage::where('id',$signal->package);

                    $this->sendNotificationToPackageSubscribers($package,$signal);
                }
            }
        }
    }

    public function sendNotificationToAllSubscribers($signal)
    {
        //lets fetch all the users
        $users = User::where('enrolledInSignal',1)->get();
        foreach ($users as $user) {
            $user->notify(new UserNotification($user,$signal->message,$signal->subject));
        }
        $signal->broadCasted=1;
        $signal->save();
    }
    public function sendNotificationToPackageSubscribers($package,$signal)
    {
        //lets fetch all the users
        $users = User::where(['enrolledInSignal'=>1,'packageEnrolled'=>$package->id])->get();
        foreach ($users as $user) {
            $user->notify(new UserNotification($user,$signal->message,$signal->subject));
        }
        $signal->broadCasted=1;
        $signal->save();
    }
}
