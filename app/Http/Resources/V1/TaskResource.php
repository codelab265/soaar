<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'objective_id' => $this->objective_id,
            'title' => $this->title,
            'difficulty' => $this->difficulty->value,
            'minimum_duration' => $this->minimum_duration,
            'points_value' => $this->points_value,
            'effective_points' => $this->effectivePoints(),
            'status' => $this->status->value,
            'repetition_count' => $this->repetition_count,
            'repetition_decay' => (float) $this->repetition_decay,
            'scheduled_date' => $this->scheduled_date?->toDateString(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
