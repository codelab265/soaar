<?php

use App\Filament\Resources\Subscriptions\Pages\CreateSubscription;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Models\Subscription;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('can list subscriptions', function () {
    $subscriptions = Subscription::factory()->count(3)->create();

    Livewire::test(ListSubscriptions::class)
        ->assertCanSeeTableRecords($subscriptions);
});

it('can create a subscription', function () {
    $user = User::factory()->create();

    Livewire::test(CreateSubscription::class)
        ->fillForm([
            'user_id' => $user->id,
            'tier' => 'free',
            'status' => 'active',
            'price_mwk' => 0,
            'starts_at' => now()->toDateTimeString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Subscription::where('user_id', $user->id)->exists())->toBeTrue();
});
