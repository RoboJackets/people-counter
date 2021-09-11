<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Space;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SpaceManagerMorningReport extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The space to report on.
     *
     * @var \App\Models\Space
     */
    private $space;

    /**
     * Create a new notification instance.
     *
     * @param  \App\Models\Space  $space  The space to report on
     * @return void
     */
    public function __construct(Space $space)
    {
        $this->space = $space;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the emails representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->from('noreply@my.robojackets.org', 'SCC Governing Board')
            ->replyTo('developers@robojackets.org')
            ->subject('Morning Report for '.$this->space->name)
            ->markdown('emails.spacemanagermorningreport', ['space' => $this->space]);
    }
}
