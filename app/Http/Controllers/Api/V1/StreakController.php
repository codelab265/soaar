<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\StreakResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StreakController
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return StreakResource::collection(
            $request->user()->streaks()->get()
        );
    }
}
