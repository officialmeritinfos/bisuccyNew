<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepositMail extends Notification
{
    use Queueable;

    public $user;
    public $coin;
    public $data;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user,$coin,$data)
    {
        $this->user = $user;
        $this->coin = $coin;
        $this->data = $data;
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
        $user = $this->user;
        $coin = $this->coin;
        $amount = $this->data['amount'];
        $img = asset('cryptocoins/'.strtolower($coin->icon).'.svg');
        if ($user->notification ==1) {
            return (new MailMessage)
                ->subject(number_format($amount,$coin->precision).' '.$coin->name.' Deposit Confirmed')
                ->line('<p style="text-align:center;"><img src='.$img.' alt='.$coin->name.'></p><br>')
                ->greeting( '<p style="text-align:center;">'.number_format($amount,$coin->precision).' '.$coin->name.'</p>')
                ->line('<p style="text-align:center;">
                        Your '.env('APP_NAME').' wallet has received some tokens, and has been credited
                        to your account.</p>');
        }
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
