<?php

declare(strict_types=1);

namespace App\Import;

use App\Import\Feed\FeedRegistry;
use App\Import\Feed\PickupPointFeedReader;
use App\Import\ImportProgressReporter\ImportProgressReporter;
use App\PickupPoint\Carrier;
use App\PickupPoint\Country;
use App\PickupPoint\PickupPoint;
use App\PickupPoint\PickupPointRepository;
use App\PickupPoint\PickupPointStatus;
use App\PickupPoint\PickupPointType;
use DateTimeImmutable;
use Generator;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

final class PickupPointImporterTest extends TestCase
{
    public function testFlushesToTheRepositoryInBatchesOfOneHundred(): void
    {
        $feedReader = $this->createFeedReaderStub(101);
        $pickupPointRepository = new class () implements PickupPointRepository {
            /** @var list<int> */
            public array $batchSizes = [];

            public function upsert(array $points): void
            {
                $this->batchSizes[] = count($points);
            }

            public function markStaleAsTemporarilyUnavailable(
                Carrier $carrier,
                Country $country,
                DateTimeImmutable $importStartedAt
            ): void {
            }
        };

        $progressReporter = $this->createStub(ImportProgressReporter::class);
        $importer = $this->createImporter($feedReader, $pickupPointRepository);

        $importer->import(Carrier::BALIKOVNA, $progressReporter);

        // 101 points → one full batch of 100, then the remaining 1.
        Assert::assertSame([100, 1], $pickupPointRepository->batchSizes);
    }

    public function testMarksStaleOnceForTheCountryNotPerPoint(): void
    {
        $feedReader = $this->createFeedReaderStub(150);
        $repository = $this->createMock(PickupPointRepository::class);
        $repository->expects($this->once())
            ->method('markStaleAsTemporarilyUnavailable')
            ->with(
                Carrier::BALIKOVNA,
                Country::CZ,
                $this->isInstanceOf(DateTimeImmutable::class),
            );

        $importer = $this->createImporter($feedReader, $repository);
        $progressReporter = $this->createStub(ImportProgressReporter::class);

        $importer->import(Carrier::BALIKOVNA, $progressReporter);
    }

    public function testReportsProgressInOrderWithCumulativeCounts(): void
    {
        $feedReader = $this->createFeedReaderStub(101);
        $progressReporter = new class () implements ImportProgressReporter {
            /** @var list<string> */
            public array $calls = [];

            public function preparing(): void
            {
                $this->calls[] = 'preparing';
            }

            public function start(int $total): void
            {
                $this->calls[] = "start({$total})";
            }

            public function setProgress(int $imported): void
            {
                $this->calls[] = "setProgress({$imported})";
            }

            public function finish(): void
            {
                $this->calls[] = 'finish';
            }
        };

        $importer = $this->createImporter(
            $feedReader,
            $this->createStub(PickupPointRepository::class),
        );

        $importer->import(Carrier::BALIKOVNA, $progressReporter);

        // 101 points → progress is reported after each flush: the batch of 100, then 101.
        Assert::assertSame(
            ['preparing', 'start(101)', 'setProgress(100)', 'setProgress(101)', 'finish'],
            $progressReporter->calls,
        );
    }

    public function testEmptyFeedReturnsEarlyWithoutStreamingUpsertingOrMarkingStale(): void
    {
        $feedReader = $this->createMock(PickupPointFeedReader::class);
        $feedReader->method('getNumberOfPickupPoints')
            ->willReturn(0);

        $feedReader->expects($this->never())
            ->method('getPickupPoints');

        $repository = $this->createMock(PickupPointRepository::class);
        $repository->expects($this->never())
            ->method('upsert');
        $repository->expects($this->never())
            ->method('markStaleAsTemporarilyUnavailable');

        $progressReporter = $this->createMock(ImportProgressReporter::class);
        $progressReporter->expects($this->once())
            ->method('preparing');
        $progressReporter->expects($this->once())
            ->method('start')->with(0);
        $progressReporter->expects($this->never())
            ->method('setProgress');
        $progressReporter->expects($this->once())
            ->method('finish');

        $importer = $this->createImporter($feedReader, $repository);

        $importer->import(Carrier::BALIKOVNA, $progressReporter);
    }

    private function createImporter(
        PickupPointFeedReader $feedReader,
        PickupPointRepository $repository,
    ): PickupPointImporter {
        $registry = $this->createMock(FeedRegistry::class);
        $registry->expects($this->once())
            ->method('getFeed')
            ->with(Carrier::BALIKOVNA)
            ->willReturn($feedReader);

        return new PickupPointImporter($registry, $repository);
    }

    private function createFeedReaderStub(int $numberOfPickupPoints): PickupPointFeedReader
    {
        $points = array_map(
            fn (int $i): PickupPoint => $this->createPickupPoint((string) $i),
            $numberOfPickupPoints > 0 ? range(1, $numberOfPickupPoints) : [],
        );

        $feedReader = $this->createStub(PickupPointFeedReader::class);
        $feedReader->method('getNumberOfPickupPoints')
            ->willReturn($numberOfPickupPoints);
        $feedReader->method('getPickupPoints')
            ->willReturnCallback(static function () use ($points): Generator {
                yield from $points;
            });

        return $feedReader;
    }

    private function createPickupPoint(string $externalId): PickupPoint
    {
        return new PickupPoint(
            externalId: $externalId,
            carrier: Carrier::BALIKOVNA,
            type: PickupPointType::BOX,
            status: PickupPointStatus::AVAILABLE,
            city: 'Praha',
            name: 'Test Point',
            address: 'Test Address',
            zipCode: '10000',
            country: Country::CZ,
            latitude: '50.00000000',
            longitude: '14.00000000',
        );
    }
}
