<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Money = 'money';
    case Points = 'points';
    case Hybrid = 'hybrid';
}
