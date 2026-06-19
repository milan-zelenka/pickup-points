<?php

declare(strict_types=1);

namespace App\PickupPoint;

enum Carrier: string
{
    case BALIKOVNA = 'balikovna';

    /** @return list<string> */
    public static function getCarrierNames(): array
    {
        return array_map(static fn (self $carrier): string => $carrier->value, self::cases());
    }
}
