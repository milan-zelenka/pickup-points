<?php

declare(strict_types=1);

namespace App\Import\Carrier\Balikovna;

use App\PickupPoint\PickupPointStatus;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class PickupPointStatusMapperTest extends TestCase
{
    #[DataProvider('knownOperationalValuesProvider')]
    public function testMapsKnownOperationalValueToAvailable(string $input): void
    {
        $mapper = new PickupPointStatusMapper();

        $result = $mapper->map($input);

        Assert::assertSame(PickupPointStatus::AVAILABLE, $result);
    }

    /** @return iterable<string, array{string}> */
    public static function knownOperationalValuesProvider(): iterable
    {
        yield 'empty (no STAV)'  => [''];
        yield 'nová'             => ['nová'];
        yield 'často vytížená'   => ['často vytížená'];
    }

    public function testThrowsOnUnknownValue(): void
    {
        $mapper = new PickupPointStatusMapper();

        $this->expectExceptionObject(
            new UnexpectedValueException('Unknown Balíkovna STAV value: "zavřeno"'),
        );

        $mapper->map('zavřeno');
    }
}
