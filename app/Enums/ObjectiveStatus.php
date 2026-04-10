<?php

namespace App\Enums;

enum ObjectiveStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Verified = 'verified';
}
