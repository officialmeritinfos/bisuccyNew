<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalMailLater extends Notification
{
    use Queueable;
    public $user;
    public $message;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user,$message)
    {
        $this->user = $user;
        $this->message = $message;
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
                    ->subject('Your wallet was debited')
                    ->greeting('Hello '.$this->user->name)
                    ->line('Your '.env('APP_NAME').' crypto wallet has been debited successfully.
                    See the transaction details below: <br>')
                    ->line($this->message)
                    ->line('<b>Withdrawal Processing Times</b><br>')
                    ->line('')
                    ->line('We usually process and complete cryptocurrency withdrawals within 1 hour.<br>')
                    ->line('Please, note that we keep a limited amount of cryptocurrency in our online hot wallet
                        for security reasons.<br>')
                    ->line('If your withdrawal request is higher than the balance we have available, we will need
                        to add cryptocurrency to our hot wallet and/or manually approve your withdrawal which could
                        take up to 24 hours or more in rare cases.<br>');
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
