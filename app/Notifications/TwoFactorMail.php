<?php

namespace App\Notifications;

use App\Models\TwoFactor;
use App\Traits\PubFunctions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorMail extends Notification
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
        $code = $this->generateToken('two_factors','token');
        $dataCode = [
            'user'=>$this->user->id,
            'token'=>sha1($code),
            'email'=>$this->user->email,
        ];
        TwoFactor::create($dataCode);

        return (new MailMessage)
            ->subject('Login Authentication')
            ->greeting('Hello '.$this->user->name)
            ->line('There is a Login request on your '.env('APP_NAME').' account.
                However, there is a Two factor authentication on your account.  Use the code below to
                authenticate this request.<br>')
            ->line('<b>'.$code.'</b><br>')
            ->line('Do not share this code with anybody via mail, sms, or phone call. None of our
            staff will ever ask for it either. Be vigilant!<br>')
            ->line('Thank you for choosing '.env('APP_NAME'));
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
