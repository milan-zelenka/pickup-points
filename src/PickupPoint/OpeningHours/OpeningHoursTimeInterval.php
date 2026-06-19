<?php

declare(strict_types=1);

namespace App\PickupPoint\OpeningHours;

readonly class OpeningHoursTimeInterval
{
    public function __construct(
        public string $from,
        public string $to,
    ) {
    }
}
