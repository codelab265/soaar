<?php

namespace App\Notifications;

use App\Models\Goal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GoalProofSubmittedNotification extends Notification
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
        return (new MailMessage)
            ->subject("Proof submitted for goal: {$this->goal->title}")
            ->line("{$this->goal->user->name} submitted proof for \"{$this->goal->title}\".")
            ->action('Review Submission', url('/'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'goal_id' => $this->goal->id,
            'title' => $this->goal->title,
            'message' => "Proof submitted for goal: {$this->goal->title}",
        ];
    }
}
