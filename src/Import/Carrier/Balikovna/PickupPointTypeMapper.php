<?php

declare(strict_types=1);

namespace App\Import\Carrier\Balikovna;

use App\PickupPoint\PickupPointType;
use UnexpectedValueException;

class PickupPointTypeMapper
{
    /** @throws UnexpectedValueException */
    public function map(string $typ): PickupPointType
    {
        return match ($typ) {
            'balíkovna-BOX' => PickupPointType::BOX,
            'balíkovna partner', 'pošta', 'depo' => PickupPointType::POINT,
            default => throw new UnexpectedValueException(
                sprintf('Unknown Balíkovna TYP value: "%s"', $typ),
            ),
        };
    }
}
