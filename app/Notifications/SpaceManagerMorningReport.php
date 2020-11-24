<?php

namespace App\Notifications;

use App\Space;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SpaceManagerMorningReport extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The space to report on
     *
     * @var \App\Space
     */
    private $space;

    /**
     * Create a new notification instance.
     *
     * @param \App\Space $space The space to report on
     *
     * @return void
     */
    public function __construct(Space $space)
    {
        $this->space = $space;
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
     * Get the emails representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())->markdown('emails.spacemanagermorningreport', ['space' => $this->space]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [];
    }
}
