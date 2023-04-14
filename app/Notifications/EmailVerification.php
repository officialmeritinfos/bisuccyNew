<?php

namespace App\Notifications;

use App\Traits\PubFunctions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerification extends Notification
{
    use Queueable,PubFunctions;
    public $user;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {

        $token = $this->generateToken('email_verifications','token');
        $data =[
            'email'=>$this->user->email,'user'=>$this->user->id,
            'token'=>sha1($token)
        ];
        \App\Models\EmailVerification::create($data);

        return (new MailMessage)
                    ->greeting('Hello <b>'.$this->user->name.'</b>')
                    ->line('Your account on <b>'.env('APP_NAME').'</b> has been successfully created. Use the
                    code below to verify your account.<br>')
                    ->line('<span style="text-align:center; font-weight: bold;">'.$token.'</span><br><br>')
                    ->line('Thanks for joining <b>'.env('APP_NAME').'</b>.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
