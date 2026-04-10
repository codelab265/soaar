<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\PointTransactionResource;
use App\Services\PointsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PointTransactionController
{
    public function __construct(
        private PointsService $pointsService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $transactions = $request->user()
            ->pointTransactions()
            ->latest()
            ->paginate(20);

        return PointTransactionResource::collection($transactions);
    }

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'total_points' => $user->total_points,
            'daily_earned' => $this->pointsService->dailyTaskPointsEarned($user),
            'daily_remaining' => $this->pointsService->remainingDailyTaskPoints($user),
            'daily_cap' => PointsService::DAILY_TASK_CAP,
        ]);
    }
}
