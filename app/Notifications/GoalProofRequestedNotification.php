<?php

namespace App\Notifications;

use App\Models\Goal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GoalProofRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Goal $goal,
        public string $message,
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
            ->subject("Proof requested for goal: {$this->goal->title}")
            ->line("Your accountability partner requested proof for \"{$this->goal->title}\".")
            ->line($this->message)
            ->action('Submit Proof', url('/'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'goal_id' => $this->goal->id,
            'title' => $this->goal->title,
            'proof_request_message' => $this->message,
            'message' => "Proof requested for goal: {$this->goal->title}",
        ];
    }
}
