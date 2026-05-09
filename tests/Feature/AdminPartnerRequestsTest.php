<?php

use App\Enums\PartnerRequestStatus;
use App\Models\AccountabilityPartnerRequest;
use App\Models\Goal;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['inertia.testing.ensure_pages_exist' => false]);

    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('renders partner requests with summary counts', function () {
    AccountabilityPartnerRequest::factory()->create();
    AccountabilityPartnerRequest::factory()->accepted()->create();
    AccountabilityPartnerRequest::factory()->declined()->create();

    $this->get('/admin/partner-requests')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/partner-requests')
            ->where('filters.search', '')
            ->where('filters.status', 'all')
            ->where('summary.total', 3)
            ->where('summary.pending', 1)
            ->where('summary.accepted', 1)
            ->where('summary.declined', 1)
            ->has('requests.data', 3)
        );
});

it('filters partner requests by status and search', function () {
    $matchingRequester = User::factory()->create(['name' => 'Avery Matching']);
    $matchingGoal = Goal::factory()->for($matchingRequester)->create(['title' => 'Launch coaching circle']);

    AccountabilityPartnerRequest::factory()->create([
        'goal_id' => $matchingGoal->id,
        'requester_id' => $matchingRequester->id,
        'status' => PartnerRequestStatus::Pending,
    ]);

    AccountabilityPartnerRequest::factory()->accepted()->create();

    $this->get('/admin/partner-requests?status=pending&search=Avery')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.search', 'Avery')
            ->where('filters.status', 'pending')
            ->has('requests.data', 1)
            ->where('requests.data.0.requester.name', 'Avery Matching')
        );
});

it('allows admins to accept pending partner requests', function () {
    $request = AccountabilityPartnerRequest::factory()->create();

    $this->post("/admin/partner-requests/{$request->id}/accept")
        ->assertRedirect()
        ->assertSessionHas('success', 'Partner request accepted.');

    $request->refresh();

    expect($request->status)->toBe(PartnerRequestStatus::Accepted)
        ->and($request->responded_at)->not->toBeNull()
        ->and($request->goal->accountability_partner_id)->toBe($request->partner_id);
});

it('allows admins to decline pending partner requests', function () {
    $request = AccountabilityPartnerRequest::factory()->create();

    $this->post("/admin/partner-requests/{$request->id}/decline")
        ->assertRedirect()
        ->assertSessionHas('success', 'Partner request declined.');

    $request->refresh();

    expect($request->status)->toBe(PartnerRequestStatus::Declined)
        ->and($request->responded_at)->not->toBeNull();
});
