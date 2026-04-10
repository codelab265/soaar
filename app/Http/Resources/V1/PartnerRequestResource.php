<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerRequestResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'goal_id' => $this->goal_id,
            'requester_id' => $this->requester_id,
            'partner_id' => $this->partner_id,
            'status' => $this->status->value,
            'responded_at' => $this->responded_at?->toIso8601String(),
            'goal' => new GoalResource($this->whenLoaded('goal')),
            'requester' => new UserResource($this->whenLoaded('requester')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
