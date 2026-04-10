<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController
{
    public function __construct(
        private AnalyticsService $analyticsService,
    ) {}

    public function summary(Request $request): JsonResponse
    {
        return response()->json(
            $this->analyticsService->summary($request->user())
        );
    }

    public function completionRate(Request $request): JsonResponse
    {
        return response()->json([
            'completion_rate' => $this->analyticsService->completionRate($request->user()),
        ]);
    }

    public function weeklyConsistency(Request $request): JsonResponse
    {
        return response()->json([
            'weekly_consistency' => $this->analyticsService->weeklyConsistency($request->user()),
        ]);
    }

    public function pointsHistory(Request $request): JsonResponse
    {
        $days = (int) $request->query('days', 30);

        return response()->json([
            'points_history' => $this->analyticsService->pointsHistory($request->user(), $days),
        ]);
    }

    public function disciplineTrend(Request $request): JsonResponse
    {
        return response()->json([
            'discipline_trend' => $this->analyticsService->disciplineTrend($request->user()),
        ]);
    }
}
