<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StreakAtRiskNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $currentStreak,
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
            ->subject('Your streak is nervous')
            ->line("Your {$this->currentStreak}-day streak will break if you don't complete a task today!")
            ->line('Still building or already quitting?')
            ->action('Complete a Task', url('/'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'current_streak' => $this->currentStreak,
            'message' => "Your {$this->currentStreak}-day streak is at risk!",
        ];
    }
}
