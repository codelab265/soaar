<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionTier;
use Database\Factories\SubscriptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    /** @use HasFactory<SubscriptionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tier',
        'status',
        'price_mwk',
        'starts_at',
        'ends_at',
        'cancelled_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tier' => SubscriptionTier::class,
            'status' => SubscriptionStatus::class,
            'price_mwk' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Determine if this subscription is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::Active
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    /**
     * Determine if this subscription is premium.
     */
    public function isPremium(): bool
    {
        return $this->tier === SubscriptionTier::Premium && $this->isActive();
    }
}
