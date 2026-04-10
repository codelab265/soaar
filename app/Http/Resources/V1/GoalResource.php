<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GoalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'why' => $this->why,
            'category' => $this->category,
            'deadline' => $this->deadline->toDateString(),
            'status' => $this->status->value,
            'accountability_partner_id' => $this->accountability_partner_id,
            'objectives_count' => $this->whenCounted('objectives'),
            'accountability_partner' => new UserResource($this->whenLoaded('accountabilityPartner')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
