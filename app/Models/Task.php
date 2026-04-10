<?php

namespace App\Models;

use App\Enums\TaskDifficulty;
use App\Enums\TaskStatus;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'objective_id',
        'title',
        'difficulty',
        'minimum_duration',
        'points_value',
        'status',
        'repetition_count',
        'repetition_decay',
        'scheduled_date',
        'completed_at',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'pending',
        'repetition_count' => 0,
        'repetition_decay' => 1.00,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'difficulty' => TaskDifficulty::class,
            'status' => TaskStatus::class,
            'scheduled_date' => 'date',
            'completed_at' => 'datetime',
            'repetition_decay' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Objective, $this>
     */
    public function objective(): BelongsTo
    {
        return $this->belongsTo(Objective::class);
    }

    /**
     * @return MorphMany<PointTransaction, $this>
     */
    public function pointTransactions(): MorphMany
    {
        return $this->morphMany(PointTransaction::class, 'transactionable');
    }

    /**
     * Calculate the effective points after applying repetition decay.
     */
    public function effectivePoints(): int
    {
        return (int) round($this->points_value * $this->repetition_decay);
    }
}
