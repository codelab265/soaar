<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InactivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $inactiveDays,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Comfort zone detected')
            ->line("You've been inactive for {$this->inactiveDays} day(s).")
            ->line('Your streak is nervous. Come back and keep building.')
            ->action('Get Back On Track', url('/'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'inactive_days' => $this->inactiveDays,
            'message' => "You've been inactive for {$this->inactiveDays} day(s). Comfort zone detected.",
        ];
    }
}
