<?php

declare(strict_types=1);

namespace App\Import\Carrier\Balikovna;

use App\Import\Feed\PickupPointFeedReader;
use App\Import\Feed\PickupPointFeedException;
use App\Import\Feed\XmlFeedStreamer;
use App\PickupPoint\Carrier;
use App\PickupPoint\Country;
use App\PickupPoint\PickupPoint;
use Generator;
use RuntimeException;
use SimpleXMLElement;

class BalikovnaPickupPointFeedReader implements PickupPointFeedReader
{
    private const string FEED_URL = 'http://napostu.ceskaposta.cz/vystupy/balikovny.xml';
    private const string NODE_IDENTIFIER = 'row';

    public function __construct(
        private readonly XmlFeedStreamer $streamer,
        private readonly PickupPointTypeMapper $typeMapper,
        private readonly PickupPointStatusMapper $statusMapper,
        private readonly OpeningHoursParser $openingHoursParser,
        private readonly ZipCodeParser $zipCodeParser,
    ) {
    }

    public function getCarrier(): Carrier
    {
        return Carrier::BALIKOVNA;
    }

    public function getNumberOfPickupPoints(): int
    {
        try {
            return $this->streamer->countNodes(self::FEED_URL, self::NODE_IDENTIFIER);
        } catch (RuntimeException $exception) {
            throw PickupPointFeedException::cannotReadFeed(self::FEED_URL, $exception);
        }
    }

    /** @return Generator<PickupPoint> */
    public function getPickupPoints(): Generator
    {
        try {
            foreach ($this->streamer->streamNodes(self::FEED_URL, self::NODE_IDENTIFIER) as $element) {
                yield $this->createPickupPoint($element);
            }
        } catch (RuntimeException $exception) {
            throw PickupPointFeedException::cannotReadFeed(self::FEED_URL, $exception);
        }
    }

    /**
     * @throws RuntimeException
     */
    private function createPickupPoint(SimpleXMLElement $node): PickupPoint
    {
        // <PSC> seems to be Czech Post's internal branch routing code, unique per pickup point.
        // It is not the geographic postal code; the real zip lives inside <ADRESA>.
        $externalId = trim((string) $node->PSC);
        $address = trim((string) $node->ADRESA);

        return new PickupPoint(
            externalId: $externalId,
            carrier: $this->getCarrier(),
            type: $this->typeMapper->map((string) $node->TYP),
            status: $this->statusMapper->map(trim((string) $node->STAV)),
            city: trim((string) $node->OBEC),
            name: trim((string) $node->NAZEV),
            address: $address,
            zipCode: $this->zipCodeParser->parse($address),
            country: Country::CZ,
            latitude: trim((string) $node->SOUR_Y_WGS84),
            longitude: trim((string) $node->SOUR_X_WGS84),
            openingHours: $this->openingHoursParser->parse($node->OTEV_DOBY),
        );
    }
}
