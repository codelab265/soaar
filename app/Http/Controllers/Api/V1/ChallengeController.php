<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ChallengeStatus;
use App\Http\Resources\V1\ChallengeResource;
use App\Models\Challenge;
use App\Services\ChallengeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ChallengeController
{
    public function __construct(
        private ChallengeService $challengeService,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return ChallengeResource::collection(
            Challenge::where('status', ChallengeStatus::Active)
                ->withCount('users')
                ->latest()
                ->paginate(15)
        );
    }

    public function show(Challenge $challenge): ChallengeResource
    {
        $challenge->loadCount('users');

        return new ChallengeResource($challenge);
    }

    public function join(Request $request, Challenge $challenge): JsonResponse
    {
        $pivot = $this->challengeService->joinChallenge($request->user(), $challenge);

        return response()->json([
            'message' => 'Joined challenge successfully.',
            'joined_at' => $pivot->joined_at,
        ]);
    }

    public function complete(Request $request, Challenge $challenge): JsonResponse
    {
        $pivot = $this->challengeService->completeChallenge($request->user(), $challenge);

        return response()->json([
            'message' => 'Challenge completed! Points awarded.',
            'completed_at' => $pivot->completed_at,
        ]);
    }

    public function progress(Request $request, Challenge $challenge): JsonResponse
    {
        return response()->json(
            $this->challengeService->checkProgress($request->user(), $challenge)
        );
    }
}
