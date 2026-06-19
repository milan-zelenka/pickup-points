<?php

declare(strict_types=1);

namespace App\PickupPoint;

enum PickupPointType: string
{
    case BOX = 'box';
    case POINT = 'point';
}
