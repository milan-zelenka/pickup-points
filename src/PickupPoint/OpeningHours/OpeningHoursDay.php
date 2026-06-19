<?php

declare(strict_types=1);

namespace App\PickupPoint\OpeningHours;

enum OpeningHoursDay: string
{
    case MON = 'Mon';
    case TUE = 'Tue';
    case WED = 'Wed';
    case THU = 'Thu';
    case FRI = 'Fri';
    case SAT = 'Sat';
    case SUN = 'Sun';
}
