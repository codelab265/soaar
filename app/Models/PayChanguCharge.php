<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayChanguCharge extends Model
{
    protected $table = 'paychangu_charges';

    protected $fillable = [
        'user_id',
        'course_id',
        'purpose',
        'payment_method',
        'charge_id',
        'ref_id',
        'currency',
        'amount',
        'points_reserved',
        'status',
        'provider_initialize_response',
        'provider_verify_response',
        'provider_webhook_payload',
        'last_webhook_fingerprint',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'points_reserved' => 'integer',
            'provider_initialize_response' => 'array',
            'provider_verify_response' => 'array',
            'provider_webhook_payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
