<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('can list users', function () {
    $users = User::factory(3)->create();

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords($users);
});

it('can create a user with profile picture', function () {
    Storage::fake('public');

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'New User',
            'username' => 'newuser',
            'email' => 'new@example.com',
            'password' => 'password123',
            'profile_picture' => [UploadedFile::fake()->image('avatar.jpg', 200, 200)],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'new@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->profile_picture)->not->toBeNull();
});

it('can create a user without profile picture', function () {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'No Picture User',
            'username' => 'nopicuser',
            'email' => 'nopic@example.com',
            'password' => 'password123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(User::where('email', 'nopic@example.com')->exists())->toBeTrue();
});

it('can update a user profile picture', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'profile_picture' => [UploadedFile::fake()->image('new-avatar.jpg', 200, 200)],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->profile_picture)->not->toBeNull();
});

it('validates required fields on create', function () {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => null,
            'username' => null,
            'email' => null,
            'password' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'username' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);
});
