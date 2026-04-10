<?php

namespace App\Enums;

enum TaskDifficulty: string
{
    case Simple = 'simple';
    case Medium = 'medium';
    case Hard = 'hard';

    /**
     * Get the base points awarded for this difficulty.
     */
    public function points(): int
    {
        return match ($this) {
            self::Simple => 5,
            self::Medium => 10,
            self::Hard => 20,
        };
    }
}
