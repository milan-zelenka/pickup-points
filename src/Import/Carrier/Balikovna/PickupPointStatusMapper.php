<?php

declare(strict_types=1);

namespace App\Import\Carrier\Balikovna;

use App\PickupPoint\PickupPointStatus;
use UnexpectedValueException;

class PickupPointStatusMapper
{
    /**
     * @throws UnexpectedValueException
     */
    public function map(string $status): PickupPointStatus
    {
        return match ($status) {
            '', 'nová', 'často vytížená' => PickupPointStatus::AVAILABLE,
            default => throw new UnexpectedValueException(
                sprintf('Unknown Balíkovna STAV value: "%s"', $status),
            ),
        };
    }
}
