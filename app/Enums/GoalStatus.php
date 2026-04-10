<?php

namespace App\Enums;

enum GoalStatus: string
{
    case Active = 'active';
    case PendingVerification = 'pending_verification';
    case VerifiedCompleted = 'verified_completed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
}
