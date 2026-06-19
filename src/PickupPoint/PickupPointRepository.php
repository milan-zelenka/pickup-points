<?php

declare(strict_types=1);

namespace App\PickupPoint;

use DateTimeImmutable;

interface PickupPointRepository
{
    /** @param list<PickupPoint> $points */
    public function upsert(array $points): void;

    public function markStaleAsTemporarilyUnavailable(
        Carrier $carrier,
        Country $country,
        DateTimeImmutable $importStartedAt,
    ): void;
}
