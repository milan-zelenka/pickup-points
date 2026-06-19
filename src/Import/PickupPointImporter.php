<?php

declare(strict_types=1);

namespace App\Import;

use App\Import\Feed\FeedRegistry;
use App\Import\ImportProgressReporter\ImportProgressReporter;
use App\PickupPoint\Carrier;
use App\PickupPoint\Country;
use App\PickupPoint\PickupPointRepository;
use DateTimeImmutable;

class PickupPointImporter implements Importer
{
    private const int BATCH_SIZE = 100;

    public function __construct(
        private readonly FeedRegistry $feedRegistry,
        private readonly PickupPointRepository $repository,
    ) {
    }

    public function import(Carrier $carrier, ImportProgressReporter $progress): void
    {
        $feedReader = $this->feedRegistry->getFeed($carrier);

        $progress->preparing();
        $total = $feedReader->getNumberOfPickupPoints();
        $progress->start($total);

        if ($total === 0) {
            $progress->finish();

            return;
        }

        $importStartedAt = new DateTimeImmutable();
        $batch = [];
        $imported = 0;
        /** @var array<string, Country> $countriesSeen */
        $countriesSeen = [];

        foreach ($feedReader->getPickupPoints() as $pickupPoint) {
            $countriesSeen[$pickupPoint->country->value] = $pickupPoint->country;
            $batch[] = $pickupPoint;

            if (count($batch) === self::BATCH_SIZE) {
                $this->repository->upsert($batch);
                $imported += count($batch);
                $batch = [];
                $progress->setProgress($imported);
            }
        }

        if ($batch !== []) {
            $this->repository->upsert($batch);
            $imported += count($batch);
            $progress->setProgress($imported);
        }

        foreach ($countriesSeen as $country) {
            $this->repository->markStaleAsTemporarilyUnavailable($carrier, $country, $importStartedAt);
        }

        $progress->finish();
    }
}
