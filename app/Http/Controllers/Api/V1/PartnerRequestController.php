<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\PartnerRequestResource;
use App\Models\AccountabilityPartnerRequest;
use App\Models\Goal;
use App\Models\User;
use App\Services\AccountabilityPartnerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PartnerRequestController
{
    public function __construct(
        private AccountabilityPartnerService $partnerService,
    ) {}

    public function store(Request $request, Goal $goal): JsonResponse
    {
        abort_unless($goal->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'partner_id' => ['required', 'exists:users,id'],
        ]);

        $partnerRequest = $this->partnerService->sendRequest(
            $goal,
            User::findOrFail($data['partner_id'])
        );

        return (new PartnerRequestResource($partnerRequest))
            ->response()
            ->setStatusCode(201);
    }

    public function incoming(Request $request): AnonymousResourceCollection
    {
        $requests = AccountabilityPartnerRequest::where('partner_id', $request->user()->id)
            ->with(['goal', 'requester'])
            ->latest()
            ->paginate(15);

        return PartnerRequestResource::collection($requests);
    }

    public function outgoing(Request $request): AnonymousResourceCollection
    {
        $requests = AccountabilityPartnerRequest::where('requester_id', $request->user()->id)
            ->with(['goal', 'partner'])
            ->latest()
            ->paginate(15);

        return PartnerRequestResource::collection($requests);
    }

    public function accept(Request $request, AccountabilityPartnerRequest $partnerRequest): PartnerRequestResource
    {
        abort_unless($partnerRequest->partner_id === $request->user()->id, 403);

        return new PartnerRequestResource(
            $this->partnerService->acceptRequest($partnerRequest)
        );
    }

    public function decline(Request $request, AccountabilityPartnerRequest $partnerRequest): PartnerRequestResource
    {
        abort_unless($partnerRequest->partner_id === $request->user()->id, 403);

        return new PartnerRequestResource(
            $this->partnerService->declineRequest($partnerRequest)
        );
    }
}
