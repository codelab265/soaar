<?php

namespace App\Providers;

use App\Models\User;
use App\Services\FcmPushService;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(NotificationSent::class, function (NotificationSent $event): void {
            if ($event->channel !== 'database' || ! $event->notifiable instanceof User) {
                return;
            }

            /** @var array<string, mixed> $payload */
            $payload = $event->notification->toArray($event->notifiable);
            $message = $payload['message'] ?? 'You have a new update in SoaaR!';

            app(FcmPushService::class)->sendToUser(
                user: $event->notifiable,
                title: 'SoaaR!',
                body: is_string($message) ? $message : 'You have a new update in SoaaR!',
                data: [
                    'notification_type' => class_basename($event->notification),
                ],
            );
        });
    }
}
