<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StreakResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'current_count' => $this->current_count,
            'longest_count' => $this->longest_count,
            'last_activity_date' => $this->last_activity_date?->toDateString(),
            'started_at' => $this->started_at,
        ];
    }
}
