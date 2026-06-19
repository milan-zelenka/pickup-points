<?php

declare(strict_types=1);

namespace App\PickupPoint;

use App\PickupPoint\OpeningHours\OpeningHoursDaySchedule;
use App\PickupPoint\OpeningHours\OpeningHoursTimeInterval;
use DateTimeImmutable;
use JsonException;
use PDO;
use Throwable;

readonly class PdoPickupPointRepository implements PickupPointRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @param list<PickupPoint> $points
     * @throws Throwable
     * @throws JsonException
     */
    public function upsert(array $points): void
    {
        $sql = <<<'SQL'
            INSERT INTO pickup_points
                (externalId, carrier, type, status, city, name, address, zipCode,
                 country, latitude, longitude, openingHours, created, lastSeenAt)
            VALUES
                (:externalId, :carrier, :type, :status, :city, :name, :address, :zipCode,
                 :country, :latitude, :longitude, :openingHours, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                type         = VALUES(type),
                status       = VALUES(status),
                city         = VALUES(city),
                name         = VALUES(name),
                address      = VALUES(address),
                zipCode      = VALUES(zipCode),
                latitude     = VALUES(latitude),
                longitude    = VALUES(longitude),
                openingHours = VALUES(openingHours),
                lastSeenAt   = VALUES(lastSeenAt)
            SQL;

        $stmt = $this->pdo->prepare($sql);

        $this->pdo->beginTransaction();
        try {
            foreach ($points as $point) {
                $stmt->execute([
                    ':externalId'   => $point->externalId,
                    ':carrier'      => $point->carrier->value,
                    ':type'         => $point->type->value,
                    ':status'       => $point->status->value,
                    ':city'         => $point->city,
                    ':name'         => $point->name,
                    ':address'      => $point->address,
                    ':zipCode'      => $point->zipCode,
                    ':country'      => $point->country->value,
                    ':latitude'     => $point->latitude,
                    ':longitude'    => $point->longitude,
                    ':openingHours' => $point->openingHours !== null
                        ? json_encode(
                            array_map(
                                static fn (OpeningHoursDaySchedule $d) => [
                                    'day'       => $d->day->value,
                                    'intervals' => array_map(
                                        static fn (OpeningHoursTimeInterval $i) => [
                                            'from' => $i->from,
                                            'to' => $i->to
                                        ],
                                        $d->intervals,
                                    ),
                                ],
                                $point->openingHours->daySchedules,
                            ),
                            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE,
                        )
                        : null,
                ]);
            }
            $this->pdo->commit();
        } catch (Throwable $throwable) {
            $this->pdo->rollBack();
            throw $throwable;
        }
    }

    public function markStaleAsTemporarilyUnavailable(
        Carrier $carrier,
        Country $country,
        DateTimeImmutable $importStartedAt,
    ): void {
        $stmt = $this->pdo->prepare(<<<'SQL'
            UPDATE pickup_points
            SET status = 'temporarily_unavailable'
            WHERE carrier    = :carrier
              AND country    = :country
              AND lastSeenAt < :importStartedAt
              AND status NOT IN ('temporarily_unavailable', 'closed', 'terminated')
            SQL);

        $stmt->execute([
            ':carrier'         => $carrier->value,
            ':country'         => $country->value,
            ':importStartedAt' => $importStartedAt->format('Y-m-d H:i:s'),
        ]);
    }
}
