<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SubscriptionTier;
use App\Http\Resources\V1\SubscriptionResource;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController
{
    public function __construct(
        private SubscriptionService $subscriptionService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $subscription = $this->subscriptionService->getActiveSubscription($request->user());

        if (! $subscription) {
            return response()->json(['data' => null]);
        }

        return (new SubscriptionResource($subscription))->response();
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'tier' => ['required', 'in:free,premium'],
        ]);

        $subscription = $this->subscriptionService->subscribe(
            $request->user(),
            SubscriptionTier::from($data['tier'])
        );

        return (new SubscriptionResource($subscription))
            ->response()
            ->setStatusCode(201);
    }

    public function cancel(Request $request): JsonResponse
    {
        $subscription = $this->subscriptionService->getActiveSubscription($request->user());

        abort_unless($subscription, 404, 'No active subscription found.');

        return (new SubscriptionResource(
            $this->subscriptionService->cancel($subscription)
        ))->response();
    }

    public function renew(Request $request): JsonResponse
    {
        $subscription = $this->subscriptionService->getActiveSubscription($request->user());

        abort_unless($subscription, 404, 'No active subscription found.');

        return (new SubscriptionResource(
            $this->subscriptionService->renew($subscription)
        ))->response();
    }
}
