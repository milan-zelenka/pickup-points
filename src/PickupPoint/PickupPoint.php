<?php

declare(strict_types=1);

namespace App\PickupPoint;

use App\PickupPoint\OpeningHours\OpeningHours;

readonly class PickupPoint
{
    public function __construct(
        public string $externalId,
        public Carrier $carrier,
        public PickupPointType $type,
        public PickupPointStatus $status,
        public string $city,
        public string $name,
        public string $address,
        public string $zipCode,
        public Country $country,
        public string $latitude,
        public string $longitude,
        public ?OpeningHours $openingHours = null,
    ) {
    }
}
