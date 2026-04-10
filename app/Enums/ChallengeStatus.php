<?php

namespace App\Enums;

enum ChallengeStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
