<?php

declare(strict_types=1);

namespace App\Import\Carrier\Balikovna;

use App\PickupPoint\OpeningHours\OpeningHours;
use App\PickupPoint\OpeningHours\OpeningHoursDaySchedule;
use App\PickupPoint\OpeningHours\OpeningHoursTimeInterval;
use RuntimeException;
use SimpleXMLElement;

readonly class OpeningHoursParser
{
    public function __construct(
        private OpeningHoursDayMapper $dayMapper,
    ) {
    }

    /**
     * @throws RuntimeException
     */
    public function parse(SimpleXMLElement $openingHours): ?OpeningHours
    {
        $days = [];
        foreach ($openingHours->den as $den) {
            $czechName = trim((string) $den['name']);
            if ($czechName === '') {
                continue;
            }

            $intervals = [];
            foreach ($den->od_do as $odDo) {
                $intervals[] = new OpeningHoursTimeInterval(
                    from: trim((string) $odDo->od),
                    to: trim((string) $odDo->do),
                );
            }

            $days[] = new OpeningHoursDaySchedule(
                day: $this->dayMapper->map($czechName),
                intervals: $intervals,
            );
        }

        if ($days === []) {
            return null;
        }

        return new OpeningHours($days);
    }
}
