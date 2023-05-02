<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Models\User;
use App\Notifications\AdminMail;
use App\Notifications\UserNotification;
use Illuminate\Console\Command;

class SendMessageNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:messageNotification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Broadcast message: could be to email or to user devices';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $moment = time();

        $messages = Message::where('timeToBroadcast','<=',$moment)->where('broadCasted','!=',1)->get();
        if ($messages->count()>0){
            foreach ($messages as $message) {
                if ($message->type==1){
                    $this->sendToEmail($message);
                }else{
                    $this->sendToMobile($message);
                }
            }
        }
    }
    //send to user email
    public function sendToEmail($message)
    {
        //fetch users on the platform to send them the mail
        $users = User::where(['status'=>1,'notification'=>1])->get();
        foreach ($users as $user) {
            $user->notify(new AdminMail($user,$message->content,$message->title));
        }
        $message->broadCasted=1;
        $message->save();
    }
    //send to mobile push notification
    public function sendToMobile($message)
    {
        //fetch users on the platform to send them the mail
        $users = User::where(['status'=>1,'notification'=>1])->get();
        foreach ($users as $user) {
            $user->notify(new UserNotification($user,$message->content,$message->title));
        }
        $message->broadCasted=1;
        $message->save();
    }
}
