<?php

declare(strict_types=1);

namespace App\PickupPoint\OpeningHours;

readonly class OpeningHoursDaySchedule
{
    /** @param list<OpeningHoursTimeInterval> $intervals */
    public function __construct(
        public OpeningHoursDay $day,
        public array $intervals,
    ) {
    }
}
