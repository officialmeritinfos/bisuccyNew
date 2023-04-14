<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeMail extends Notification
{
    use Queueable;
    public $name;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($name)
    {
        $this->name = $name;
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
        return (new MailMessage)
                    ->greeting('Hello '.$this->name)
                    ->line('Welcome to <b>'.env('APP_NAME').'</b><br>')
                    ->line('We are delighted to have you here. At <b>'.env('APP_NAME').'</b>, we are
                    building the Next-Gen Cryptocurrency trading platform, that enables you to <b>BUY,SELL,SWAP,SEND &
                    RECEIVE</b> cryptocurrency seamlessly. With our 24/7 online platform, you are covered at all time
                    with the best customer service and payout feature.<br>')
                    ->line('We will love to know what you think about our service. Shot us with your questions,
                    rate our application and let us know your mind.')
                    ->line('Thanks for using <b>'.env('APP_NAME').'</b>.');
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
