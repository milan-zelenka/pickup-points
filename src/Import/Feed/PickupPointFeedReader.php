<?php

declare(strict_types=1);

namespace App\Import\Feed;

use App\PickupPoint\Carrier;
use App\PickupPoint\PickupPoint;
use Generator;

interface PickupPointFeedReader
{
    public function getCarrier(): Carrier;

    public function getNumberOfPickupPoints(): int;

    /** @return Generator<PickupPoint> */
    public function getPickupPoints(): Generator;
}
