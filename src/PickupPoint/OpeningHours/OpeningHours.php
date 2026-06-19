<?php

declare(strict_types=1);

namespace App\PickupPoint\OpeningHours;

readonly class OpeningHours
{
    /** @param list<OpeningHoursDaySchedule> $daySchedules */
    public function __construct(
        public array $daySchedules,
    ) {
    }
}
