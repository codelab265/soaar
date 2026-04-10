<?php

use App\Enums\PartnerRequestStatus;
use App\Models\AccountabilityPartnerRequest;
use App\Models\Goal;
use App\Models\User;
use App\Services\AccountabilityPartnerService;

beforeEach(function () {
    $this->service = app(AccountabilityPartnerService::class);
    $this->user = User::factory()->create();
    $this->partner = User::factory()->create();
    $this->goal = Goal::factory()->for($this->user)->create();
});

it('can send a partner request', function () {
    $request = $this->service->sendRequest($this->goal, $this->partner);

    expect($request->status)->toBe(PartnerRequestStatus::Pending)
        ->and($request->goal_id)->toBe($this->goal->id)
        ->and($request->requester_id)->toBe($this->user->id)
        ->and($request->partner_id)->toBe($this->partner->id);
});

it('prevents sending request to self', function () {
    $this->service->sendRequest($this->goal, $this->user);
})->throws(InvalidArgumentException::class, 'A user cannot be their own accountability partner.');

it('prevents sending request when goal already has partner', function () {
    $this->goal->update(['accountability_partner_id' => $this->partner->id]);

    $anotherPartner = User::factory()->create();
    $this->service->sendRequest($this->goal, $anotherPartner);
})->throws(InvalidArgumentException::class, 'This goal already has an accountability partner.');

it('can accept a partner request', function () {
    $request = AccountabilityPartnerRequest::factory()->create([
        'goal_id' => $this->goal->id,
        'requester_id' => $this->user->id,
        'partner_id' => $this->partner->id,
        'status' => PartnerRequestStatus::Pending,
    ]);

    $result = $this->service->acceptRequest($request);

    expect($result->status)->toBe(PartnerRequestStatus::Accepted)
        ->and($result->responded_at)->not->toBeNull()
        ->and($this->goal->fresh()->accountability_partner_id)->toBe($this->partner->id);
});

it('can decline a partner request', function () {
    $request = AccountabilityPartnerRequest::factory()->create([
        'goal_id' => $this->goal->id,
        'requester_id' => $this->user->id,
        'partner_id' => $this->partner->id,
        'status' => PartnerRequestStatus::Pending,
    ]);

    $result = $this->service->declineRequest($request);

    expect($result->status)->toBe(PartnerRequestStatus::Declined)
        ->and($result->responded_at)->not->toBeNull()
        ->and($this->goal->fresh()->accountability_partner_id)->toBeNull();
});

it('cannot accept an already accepted request', function () {
    $request = AccountabilityPartnerRequest::factory()->accepted()->create([
        'goal_id' => $this->goal->id,
        'requester_id' => $this->user->id,
        'partner_id' => $this->partner->id,
    ]);

    $this->service->acceptRequest($request);
})->throws(InvalidArgumentException::class, 'Only pending requests can be accepted.');

it('cannot decline an already declined request', function () {
    $request = AccountabilityPartnerRequest::factory()->declined()->create([
        'goal_id' => $this->goal->id,
        'requester_id' => $this->user->id,
        'partner_id' => $this->partner->id,
    ]);

    $this->service->declineRequest($request);
})->throws(InvalidArgumentException::class, 'Only pending requests can be declined.');
