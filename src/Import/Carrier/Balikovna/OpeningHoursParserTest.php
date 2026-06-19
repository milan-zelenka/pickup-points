<?php

declare(strict_types=1);

namespace App\Import\Carrier\Balikovna;

use App\PickupPoint\OpeningHours\OpeningHours;
use App\PickupPoint\OpeningHours\OpeningHoursDay;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class OpeningHoursParserTest extends TestCase
{
    public function testReturnsNullWhenNoDaysPresent(): void
    {
        $parser = new OpeningHoursParser(new OpeningHoursDayMapper());

        $result = $parser->parse(new SimpleXMLElement('<OTEV_DOBY/>'));

        Assert::assertNull($result);
    }

    public function testParsesIntervalsForMultipleDays(): void
    {
        $parser = new OpeningHoursParser(new OpeningHoursDayMapper());
        $xml = new SimpleXMLElement(
            <<<'XML'
            <OTEV_DOBY>
                <den name="Pondělí">
                    <od_do><od>09:00</od><do>19:00</do></od_do>
                </den>
                <den name="Sobota">
                    <od_do><od>09:00</od><do>14:00</do></od_do>
                </den>
            </OTEV_DOBY>
            XML,
        );

        $result = $parser->parse($xml);

        Assert::assertInstanceOf(OpeningHours::class, $result);
        Assert::assertCount(2, $result->daySchedules);

        Assert::assertSame(OpeningHoursDay::MON, $result->daySchedules[0]->day);
        $mondayIntervals = $result->daySchedules[0]->intervals;
        Assert::assertCount(1, $mondayIntervals);
        Assert::assertSame('09:00', $mondayIntervals[0]->from);
        Assert::assertSame('19:00', $mondayIntervals[0]->to);

        Assert::assertSame(OpeningHoursDay::SAT, $result->daySchedules[1]->day);
        $saturdayIntervals = $result->daySchedules[1]->intervals;
        Assert::assertCount(1, $mondayIntervals);
        Assert::assertSame('09:00', $saturdayIntervals[0]->from);
        Assert::assertSame('14:00', $saturdayIntervals[0]->to);
    }

    public function testSkipsDayWithEmptyName(): void
    {
        $parser = new OpeningHoursParser(new OpeningHoursDayMapper());
        $xml = new SimpleXMLElement(
            <<<'XML'
            <OTEV_DOBY>
                <den name="">
                    <od_do><od>09:00</od><do>12:00</do></od_do>
                </den>
                <den name="Pondělí">
                    <od_do><od>08:00</od><do>17:00</do></od_do>
                </den>
            </OTEV_DOBY>
            XML,
        );

        $result = $parser->parse($xml);

        Assert::assertInstanceOf(OpeningHours::class, $result);
        Assert::assertCount(1, $result->daySchedules);
        Assert::assertSame(OpeningHoursDay::MON, $result->daySchedules[0]->day);
    }

    public function testSupportsMultipleIntervalsPerDay(): void
    {
        $parser = new OpeningHoursParser(new OpeningHoursDayMapper());
        $xml = new SimpleXMLElement(
            <<<'XML'
            <OTEV_DOBY>
                <den name="Pondělí">
                    <od_do><od>08:00</od><do>12:00</do></od_do>
                    <od_do><od>13:00</od><do>17:00</do></od_do>
                </den>
            </OTEV_DOBY>
            XML,
        );

        $result = $parser->parse($xml);

        Assert::assertInstanceOf(OpeningHours::class, $result);
        Assert::assertCount(1, $result->daySchedules);
        Assert::assertCount(2, $result->daySchedules[0]->intervals);
        Assert::assertSame('08:00', $result->daySchedules[0]->intervals[0]->from);
        Assert::assertSame('12:00', $result->daySchedules[0]->intervals[0]->to);
        Assert::assertSame('13:00', $result->daySchedules[0]->intervals[1]->from);
        Assert::assertSame('17:00', $result->daySchedules[0]->intervals[1]->to);
    }
}
