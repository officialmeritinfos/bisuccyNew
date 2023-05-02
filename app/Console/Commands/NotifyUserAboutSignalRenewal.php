<?php

namespace App\Console\Commands;

use App\Models\SignalPackage;
use App\Models\User;
use App\Notifications\AdminMail;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NotifyUserAboutSignalRenewal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:userAboutRenewal';

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
                $renewalTime = $user->timeRenewPayment;
                $dateNow = $time;
                $date= Carbon::createFromTimestamp($renewalTime);
                $dateNow= Carbon::createFromTimestamp($dateNow);
                //check the time differences
                $dateDiff = $dateNow->diffInDays($date);

                switch ($dateDiff) {
                    case '7':
                        $this->sendWeekMail($user);
                        break;
                    case '3':
                        $this->sendThreeDaysMail($user);
                        break;
                    case '1':
                        $this->sendOneDayMail($user);
                        break;
                }
            }
        }
    }
    public function sendWeekMail($user)
    {
        $user = User::where('id',$user->id)->first();
        $signal = SignalPackage::where('id',$user->packageEnrolled)->first();
        //send mail to User
        $messageToSend = "
            Your subscription to the ".env('APP_NAME')." signal ".$signal->name." package will expire
            on ".Carbon::createFromTimestamp($user->timeRenewPayment)->format('M d Y H:i:s a').".
            Remember to renew it to avoid losing access to the signal room.
        ";
        $user->notify(new AdminMail($user,$messageToSend,
            'Signal Renewal Notification: 7 Days Reminder'));
    }
    public function sendThreeDaysMail($user)
    {
        $user = User::where('id',$user->id)->first();
        $signal = SignalPackage::where('id',$user->packageEnrolled)->first();
        //send mail to User
        $messageToSend = "
            Your subscription to the ".env('APP_NAME')." signal ".$signal->name." package will expire
            on ".Carbon::createFromTimestamp($user->timeRenewPayment)->format('M d Y H:i:s a').".
            Remember to renew it to avoid losing access to the signal room.
        ";
        $user->notify(new AdminMail($user,$messageToSend,
            'Signal Renewal Notification: 3 Days Reminder'));
    }
    public function sendOneDayMail($user)
    {
        $user = User::where('id',$user->id)->first();
        $signal = SignalPackage::where('id',$user->packageEnrolled)->first();
        //send mail to User
        $messageToSend = "
            Your subscription to the ".env('APP_NAME')." signal ".$signal->name." package will expire
            on ".Carbon::createFromTimestamp($user->timeRenewPayment)->format('M d Y H:i:s a').".
            Remember to renew it to avoid losing access to the signal room.
        ";
        $user->notify(new AdminMail($user,$messageToSend,
            'Signal Renewal Notification: 24 Hour Reminder'));
    }
}
