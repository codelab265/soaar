<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PointsChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $points,
        public string $reason,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $direction = $this->points > 0 ? 'gained' : 'lost';

        return [
            'points' => $this->points,
            'reason' => $this->reason,
            'message' => "You {$direction} ".abs($this->points)." points: {$this->reason}",
        ];
    }
}
