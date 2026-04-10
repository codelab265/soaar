<?php

namespace App\Notifications;

use App\Models\Goal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeadlineApproachingNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Goal $goal,
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
        $daysLeft = now()->diffInDays($this->goal->deadline);

        return (new MailMessage)
            ->subject("Your goal deadline is approaching — {$daysLeft} day(s) left")
            ->line("Your goal \"{$this->goal->title}\" has {$daysLeft} day(s) left.")
            ->line('Still building or already quitting?')
            ->action('View Goal', url('/'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'goal_id' => $this->goal->id,
            'title' => $this->goal->title,
            'deadline' => $this->goal->deadline->toDateString(),
            'message' => "Deadline approaching for: {$this->goal->title}",
        ];
    }
}
