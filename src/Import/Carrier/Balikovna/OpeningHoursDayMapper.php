<?php

declare(strict_types=1);

namespace App\Import\Carrier\Balikovna;

use App\PickupPoint\OpeningHours\OpeningHoursDay;
use UnexpectedValueException;

class OpeningHoursDayMapper
{
    /** @throws UnexpectedValueException */
    public function map(string $czechName): OpeningHoursDay
    {
        return match ($czechName) {
            'Pondělí' => OpeningHoursDay::MON,
            'Úterý'   => OpeningHoursDay::TUE,
            'Středa'  => OpeningHoursDay::WED,
            'Čtvrtek' => OpeningHoursDay::THU,
            'Pátek'   => OpeningHoursDay::FRI,
            'Sobota'  => OpeningHoursDay::SAT,
            'Neděle'  => OpeningHoursDay::SUN,
            default   => throw new UnexpectedValueException(
                sprintf('Unknown Balíkovna day name: "%s"', $czechName),
            ),
        };
    }
}
