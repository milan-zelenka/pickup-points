<?php

declare(strict_types=1);

namespace App\Import\Carrier\Balikovna;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ZipCodeParserTest extends TestCase
{
    #[DataProvider('validAddressesProvider')]
    public function testParsesZipCode(string $address, string $expectedZip): void
    {
        $parser = new ZipCodeParser();

        $result = $parser->parse($address);

        Assert::assertSame($expectedZip, $result);
    }

    /** @return iterable<string, array{string, string}> */
    public static function validAddressesProvider(): iterable
    {
        yield '4-part address with district' => ['Švehlova 1391/32, Hostivař, 10200, Praha 10', '10200'];
        yield '3-part address without district' => ['Nusle E18, 14000, Praha 4', '14000'];
        yield 'street with no house number' => ['Úvalská , Strašnice, 10000, Praha 10', '10000'];
    }

    #[DataProvider('invalidAddressesProvider')]
    public function testThrowsOnInvalidAddress(string $address, RuntimeException $expectedException): void
    {
        $parser = new ZipCodeParser();

        $this->expectExceptionObject($expectedException);

        $parser->parse($address);
    }

    /** @return iterable<string, array{string, RuntimeException}> */
    public static function invalidAddressesProvider(): iterable
    {
        $empty = new RuntimeException('Cannot parse zip code from ADRESA: ""');
        $singleSegment = new RuntimeException('Cannot parse zip code from ADRESA: "just one segment"');
        $nonNumericZip = new RuntimeException('Cannot parse zip code from ADRESA: "Ulice 1, Praha, ABC12, Praha 1"');

        yield 'empty' => ['', $empty];
        yield 'single segment' => ['just one segment', $singleSegment];
        yield 'non-numeric zip' => ['Ulice 1, Praha, ABC12, Praha 1', $nonNumericZip];
    }
}
