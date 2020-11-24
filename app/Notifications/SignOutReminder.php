<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class SignOutReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     *
     * @param \Illuminate\Notifications\Notifiable $notifiable
     *
     * @return array<string>
     */
    public function via(Notifiable $notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param \Illuminate\Notifications\Notifiable $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(Notifiable $notifiable)
    {
        return (new MailMessage())
            ->from('noreply@my.robojackets.org', 'SCC Governing Board')
            ->subject('SCC Sign Out Reminder')
            ->markdown('emails.signoutreminder');
    }
}
