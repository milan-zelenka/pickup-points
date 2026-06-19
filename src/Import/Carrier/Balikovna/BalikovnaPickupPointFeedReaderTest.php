<?php

declare(strict_types=1);

namespace App\Import\Carrier\Balikovna;

use App\Import\Feed\PickupPointFeedException;
use App\Import\Feed\XmlFeedStreamer;
use App\PickupPoint\Carrier;
use App\PickupPoint\Country;
use App\PickupPoint\OpeningHours\OpeningHours;
use App\PickupPoint\OpeningHours\OpeningHoursDay;
use App\PickupPoint\PickupPoint;
use App\PickupPoint\PickupPointStatus;
use App\PickupPoint\PickupPointType;
use Generator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SimpleXMLElement;

class BalikovnaPickupPointFeedReaderTest extends TestCase
{
    private const string FEED_URL = 'http://napostu.ceskaposta.cz/vystupy/balikovny.xml';
    private const string NODE = 'row';

    public function testReportsItsCarrier(): void
    {
        $reader = new BalikovnaPickupPointFeedReader(
            $this->createStub(XmlFeedStreamer::class),
            $this->createStub(PickupPointTypeMapper::class),
            $this->createStub(PickupPointStatusMapper::class),
            $this->createStub(OpeningHoursParser::class),
            $this->createStub(ZipCodeParser::class),
        );

        Assert::assertSame(Carrier::BALIKOVNA, $reader->getCarrier());
    }

    public function testCountsPickupPoints(): void
    {
        $streamer = $this->createMock(XmlFeedStreamer::class);
        $streamer->expects($this->once())
            ->method('countNodes')
            ->with(self::FEED_URL, self::NODE)
            ->willReturn(42);

        $reader = new BalikovnaPickupPointFeedReader(
            $streamer,
            $this->createStub(PickupPointTypeMapper::class),
            $this->createStub(PickupPointStatusMapper::class),
            $this->createStub(OpeningHoursParser::class),
            $this->createStub(ZipCodeParser::class),
        );

        $count = $reader->getNumberOfPickupPoints();

        Assert::assertSame(42, $count);
    }

    public function testWrapsStreamerFailureWhenCounting(): void
    {
        $streamer = $this->createMock(XmlFeedStreamer::class);
        $streamer->expects($this->once())
            ->method('countNodes')
            ->willThrowException(new RuntimeException('feed unreachable'));

        $reader = new BalikovnaPickupPointFeedReader(
            $streamer,
            $this->createStub(PickupPointTypeMapper::class),
            $this->createStub(PickupPointStatusMapper::class),
            $this->createStub(OpeningHoursParser::class),
            $this->createStub(ZipCodeParser::class),
        );

        $this->expectException(PickupPointFeedException::class);

        $reader->getNumberOfPickupPoints();
    }

    public function testPickupPointsAreYielded(): void
    {
        $node = new SimpleXMLElement(
            <<<XML
            <row>
                <PSC>someExternalId</PSC>
                <NAZEV>someName</NAZEV>
                <OBEC>someCity</OBEC>
                <ADRESA>someAddress</ADRESA>
                <TYP>someType</TYP>
                <STAV>someStatus</STAV>
                <SOUR_Y_WGS84>14.476718</SOUR_Y_WGS84>
                <SOUR_X_WGS84>50.071589</SOUR_X_WGS84>
                <OTEV_DOBY>someOpeningHours</OTEV_DOBY>
            </row>
            XML,
        );

        $streamer = $this->createMock(XmlFeedStreamer::class);
        $streamer->expects($this->once())
            ->method('streamNodes')
            ->with(self::FEED_URL, self::NODE)
            ->willReturnCallback(static function () use ($node): Generator {
                yield from [$node];
            });

        $typeMapper = $this->createMock(PickupPointTypeMapper::class);
        $typeMapper->expects($this->once())
            ->method('map')
            ->with('someType')
            ->willReturn(PickupPointType::BOX);

        $statusMapper = $this->createMock(PickupPointStatusMapper::class);
        $statusMapper->expects($this->once())
            ->method('map')
            ->with('someStatus')
            ->willReturn(PickupPointStatus::AVAILABLE);

        $openingHours = new OpeningHours([]);
        $openingHoursParser = $this->createMock(OpeningHoursParser::class);
        $openingHoursParser->expects($this->once())
            ->method('parse')
            ->with(new SimpleXMLElement('<OTEV_DOBY>someOpeningHours</OTEV_DOBY>'))
            ->willReturn($openingHours);

        $zipCodeParser = $this->createMock(ZipCodeParser::class);
        $zipCodeParser->expects($this->once())
            ->method('parse')
            ->with('someAddress')
            ->willReturn('someZipCode');

        $reader = new BalikovnaPickupPointFeedReader(
            $streamer,
            $typeMapper,
            $statusMapper,
            $openingHoursParser,
            $zipCodeParser,
        );

        $pickupPoints = iterator_to_array($reader->getPickupPoints(), false);

        Assert::assertCount(1, $pickupPoints);
        $pickupPoint = $pickupPoints[0];
        Assert::assertInstanceOf(PickupPoint::class, $pickupPoint);
        Assert::assertSame('someExternalId', $pickupPoint->externalId);
        Assert::assertSame(Carrier::BALIKOVNA, $pickupPoint->carrier);
        Assert::assertSame(PickupPointType::BOX, $pickupPoint->type);
        Assert::assertSame(PickupPointStatus::AVAILABLE, $pickupPoint->status);
        Assert::assertSame('someCity', $pickupPoint->city);
        Assert::assertSame('someName', $pickupPoint->name);
        Assert::assertSame('someAddress', $pickupPoint->address);
        Assert::assertSame('someZipCode', $pickupPoint->zipCode);
        Assert::assertSame(Country::CZ, $pickupPoint->country);
        Assert::assertSame('14.476718', $pickupPoint->latitude);
        Assert::assertSame('50.071589', $pickupPoint->longitude);
        Assert::assertSame($openingHours, $pickupPoint->openingHours);
    }

    public function testWrapsStreamerFailureWhenStreaming(): void
    {
        $streamer = $this->createMock(XmlFeedStreamer::class);
        $streamer->expects($this->once())
            ->method('streamNodes')
            ->willThrowException(new RuntimeException('feed unreachable'));

        $reader = new BalikovnaPickupPointFeedReader(
            $streamer,
            $this->createStub(PickupPointTypeMapper::class),
            $this->createStub(PickupPointStatusMapper::class),
            $this->createStub(OpeningHoursParser::class),
            $this->createStub(ZipCodeParser::class),
        );

        $this->expectException(PickupPointFeedException::class);

        iterator_to_array($reader->getPickupPoints());
    }
}
