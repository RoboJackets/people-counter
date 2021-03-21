<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExposureNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The date(s) for which notifications are being sent.
     *
     * @var string
     */
    private $date;

    /**
     * The body of the notifications being sent.
     *
     * @var string
     */
    private $body;

    /**
     * Create a new notification instance.
     *
     * @param string $date
     * @param string $body
     *
     * @return void
     */
    public function __construct(string $date, string $body)
    {
        $this->date = $date;
        $this->body = $body;
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
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $data = [
            'date' => $this->date,
            'body' => $this->body,
            'first_name' => $notifiable->first_name,
        ];

        return (new MailMessage())
            ->from('sccgb@my.robojackets.org', 'SCC Governing Board')
            ->subject('COVID-19 Notification from SCC Governing Board')
            ->markdown('emails.exposure', $data);
    }
}
