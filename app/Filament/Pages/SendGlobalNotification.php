<?php

namespace App\Filament\Pages;

use App\Enums\StreakType;
use App\Models\User;
use App\Notifications\AdminBroadcastNotification;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SendGlobalNotification extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Admin';

    protected static ?string $title = 'Send Global Notification';

    protected string $view = 'filament.pages.send-global-notification';

    /**
     * @var array{audience: string, title: string, body: string}
     */
    public array $data = [
        'audience' => 'all',
        'title' => '',
        'body' => '',
    ];

    public function mount(): void
    {
        $this->form->fill($this->data);
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('audience')
                ->options([
                    'all' => 'All users (except suspended)',
                    'inactive' => 'Inactive (2+ days)',
                    'streak_at_risk' => 'Streak at risk (yesterday activity)',
                ])
                ->required(),
            TextInput::make('title')
                ->required()
                ->maxLength(120),
            Textarea::make('body')
                ->required()
                ->rows(6)
                ->maxLength(500),
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send')
                ->label('Send')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function (): void {
                    $payload = $this->form->getState();

                    $audience = (string) $payload['audience'];
                    $title = (string) $payload['title'];
                    $body = (string) $payload['body'];

                    $query = User::query()->whereNull('suspended_at');

                    if ($audience === 'inactive') {
                        $cutoff = now()->subDays(2)->toDateString();

                        $query->whereHas('streaks', function ($q) use ($cutoff): void {
                            $q->where('type', StreakType::Daily)
                                ->where(function ($qq) use ($cutoff): void {
                                    $qq->whereNull('last_activity_date')
                                        ->orWhereDate('last_activity_date', '<=', $cutoff);
                                });
                        });
                    }

                    if ($audience === 'streak_at_risk') {
                        $yesterday = now()->subDay()->toDateString();

                        $query->whereHas('streaks', function ($q) use ($yesterday): void {
                            $q->where('type', StreakType::Daily)
                                ->where('current_count', '>', 0)
                                ->whereDate('last_activity_date', $yesterday);
                        });
                    }

                    $query->chunkById(500, function ($users) use ($title, $body): void {
                        foreach ($users as $user) {
                            $user->notify(new AdminBroadcastNotification($title, $body));
                        }
                    });

                    $this->form->fill([
                        'audience' => 'all',
                        'title' => '',
                        'body' => '',
                    ]);

                    Notification::make()
                        ->title('Notification sent.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
