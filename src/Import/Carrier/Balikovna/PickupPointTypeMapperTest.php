<?php

declare(strict_types=1);

namespace App\Import\Carrier\Balikovna;

use App\PickupPoint\PickupPointType;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class PickupPointTypeMapperTest extends TestCase
{
    #[DataProvider('knownTypeValuesProvider')]
    public function testMapsKnownValue(string $input, PickupPointType $expected): void
    {
        $mapper = new PickupPointTypeMapper();

        $result = $mapper->map($input);

        Assert::assertSame($expected, $result);
    }

    /** @return iterable<string, array{string, PickupPointType}> */
    public static function knownTypeValuesProvider(): iterable
    {
        yield 'balíkovna box'     => ['balíkovna-BOX', PickupPointType::BOX];
        yield 'balíkovna partner' => ['balíkovna partner', PickupPointType::POINT];
        yield 'pošta'             => ['pošta', PickupPointType::POINT];
        yield 'depo'              => ['depo', PickupPointType::POINT];
    }

    public function testThrowsOnUnknownValue(): void
    {
        $mapper = new PickupPointTypeMapper();

        $this->expectExceptionObject(
            new UnexpectedValueException('Unknown Balíkovna TYP value: "výdejna"'),
        );

        $mapper->map('výdejna');
    }
}
