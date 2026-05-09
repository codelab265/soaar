<?php

use App\Enums\PartnerRequestStatus;
use App\Models\AccountabilityPartnerRequest;
use App\Models\Goal;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->partner = User::factory()->create();
    $this->goal = Goal::factory()->for($this->owner)->create();
});

it('sends a partner request', function () {
    $this->actingAs($this->owner, 'sanctum')
        ->postJson("/api/v1/goals/{$this->goal->id}/partner-requests", [
            'partner_id' => $this->partner->id,
        ])
        ->assertCreated()
        ->assertJsonPath('data.status', 'pending');
});

it('lists incoming partner requests', function () {
    AccountabilityPartnerRequest::create([
        'goal_id' => $this->goal->id,
        'requester_id' => $this->owner->id,
        'partner_id' => $this->partner->id,
        'status' => PartnerRequestStatus::Pending,
    ]);

    $this->actingAs($this->partner, 'sanctum')
        ->getJson('/api/v1/partner-requests')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('lists outgoing partner requests', function () {
    AccountabilityPartnerRequest::create([
        'goal_id' => $this->goal->id,
        'requester_id' => $this->owner->id,
        'partner_id' => $this->partner->id,
        'status' => PartnerRequestStatus::Pending,
    ]);

    $this->actingAs($this->owner, 'sanctum')
        ->getJson('/api/v1/partner-requests/outgoing')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.partner.username', $this->partner->username);
});

it('accepts a partner request', function () {
    $request = AccountabilityPartnerRequest::create([
        'goal_id' => $this->goal->id,
        'requester_id' => $this->owner->id,
        'partner_id' => $this->partner->id,
        'status' => PartnerRequestStatus::Pending,
    ]);

    $this->actingAs($this->partner, 'sanctum')
        ->postJson("/api/v1/partner-requests/{$request->id}/accept")
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'accepted');
});

it('declines a partner request', function () {
    $request = AccountabilityPartnerRequest::create([
        'goal_id' => $this->goal->id,
        'requester_id' => $this->owner->id,
        'partner_id' => $this->partner->id,
        'status' => PartnerRequestStatus::Pending,
    ]);

    $this->actingAs($this->partner, 'sanctum')
        ->postJson("/api/v1/partner-requests/{$request->id}/decline")
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'declined');
});
