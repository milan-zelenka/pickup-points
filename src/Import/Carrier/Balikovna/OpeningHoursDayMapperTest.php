<?php

declare(strict_types=1);

namespace App\Import\Carrier\Balikovna;

use App\PickupPoint\OpeningHours\OpeningHoursDay;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class OpeningHoursDayMapperTest extends TestCase
{
    #[DataProvider('knownDayNamesProvider')]
    public function testMapsKnownDayName(string $input, OpeningHoursDay $expected): void
    {
        $mapper = new OpeningHoursDayMapper();

        $result = $mapper->map($input);

        Assert::assertSame($expected, $result);
    }

    /** @return iterable<string, array{string, OpeningHoursDay}> */
    public static function knownDayNamesProvider(): iterable
    {
        yield 'Pondělí' => ['Pondělí', OpeningHoursDay::MON];
        yield 'Úterý'   => ['Úterý',   OpeningHoursDay::TUE];
        yield 'Středa'  => ['Středa',  OpeningHoursDay::WED];
        yield 'Čtvrtek' => ['Čtvrtek', OpeningHoursDay::THU];
        yield 'Pátek'   => ['Pátek',   OpeningHoursDay::FRI];
        yield 'Sobota'  => ['Sobota',  OpeningHoursDay::SAT];
        yield 'Neděle'  => ['Neděle',  OpeningHoursDay::SUN];
    }

    public function testThrowsOnUnknownDayName(): void
    {
        $this->expectExceptionObject(
            new UnexpectedValueException('Unknown Balíkovna day name: "Monday"'),
        );

        $mapper = new OpeningHoursDayMapper();

        $mapper->map('Monday');
    }
}
