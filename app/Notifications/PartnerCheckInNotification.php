<?php

namespace App\Notifications;

use App\Models\Goal;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PartnerCheckInNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Goal $goal,
        public string $partnerName,
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
            ->subject("Partner check-in: {$this->goal->title}")
            ->line("{$this->partnerName} is waiting for your verification on \"{$this->goal->title}\".")
            ->action('Review Goal', url('/'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'goal_id' => $this->goal->id,
            'partner_name' => $this->partnerName,
            'message' => "{$this->partnerName} needs your verification on: {$this->goal->title}",
        ];
    }
}
