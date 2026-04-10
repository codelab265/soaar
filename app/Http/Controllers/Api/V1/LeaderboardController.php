<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\UserResource;
use App\Services\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController
{
    public function __construct(
        private LeaderboardService $leaderboardService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 10);

        return response()->json([
            'leaderboard' => UserResource::collection($this->leaderboardService->getLeaderboard($limit)),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'rank' => $this->leaderboardService->getUserRank($request->user()),
            'user' => new UserResource($request->user()),
        ]);
    }
}
