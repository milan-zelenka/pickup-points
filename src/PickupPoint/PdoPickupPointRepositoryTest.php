<?php

declare(strict_types=1);

namespace App\PickupPoint;

use App\PickupPoint\OpeningHours\OpeningHours;
use App\PickupPoint\OpeningHours\OpeningHoursDay;
use App\PickupPoint\OpeningHours\OpeningHoursDaySchedule;
use App\PickupPoint\OpeningHours\OpeningHoursTimeInterval;
use DateTimeImmutable;
use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PdoPickupPointRepositoryTest extends TestCase
{
    public function testUpsertExecutesStatementWithMappedParameters(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects($this->once())
            ->method('execute')
            ->with([
                ':externalId'   => '10007',
                ':carrier'      => Carrier::BALIKOVNA->value,
                ':type'         => PickupPointType::BOX->value,
                ':status'       => PickupPointStatus::AVAILABLE->value,
                ':city'         => 'Praha',
                ':name'         => 'Test Point',
                ':address'      => 'Test Address',
                ':zipCode'      => '10000',
                ':country'      => Country::CZ->value,
                ':latitude'     => '50.00000000',
                ':longitude'    => '14.00000000',
                ':openingHours' => null,
            ]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('prepare')
            ->with($this->logicalAnd(
                $this->stringContains('INSERT INTO pickup_points'),
                $this->stringContains('ON DUPLICATE KEY UPDATE'),
            ))
            ->willReturn($statement);
        $pdo->expects($this->once())
            ->method('beginTransaction');
        $pdo->expects($this->once())
            ->method('commit');
        $pdo->expects($this->never())
            ->method('rollBack');

        $repository = new PdoPickupPointRepository($pdo);

        $repository->upsert([$this->createPickupPoint('10007')]);
    }

    public function testUpsertExecutesOncePerPointWithASingleStatement(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects($this->exactly(3))
            ->method('execute');

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('prepare')
            ->willReturn($statement);
        $pdo->expects($this->once())
            ->method('beginTransaction');
        $pdo->expects($this->once())
            ->method('commit');

        $repository = new PdoPickupPointRepository($pdo);

        $repository->upsert([
            $this->createPickupPoint('1'),
            $this->createPickupPoint('2'),
            $this->createPickupPoint('3'),
        ]);
    }

    public function testUpsertEncodesOpeningHoursAsUnescapedJson(): void
    {
        $openingHours = new OpeningHours([
            new OpeningHoursDaySchedule(OpeningHoursDay::MON, [
                new OpeningHoursTimeInterval('09:00', '19:00'),
            ]),
            new OpeningHoursDaySchedule(OpeningHoursDay::SAT, [
                new OpeningHoursTimeInterval('09:00', '12:00'),
                new OpeningHoursTimeInterval('13:00', '14:00'),
            ]),
        ]);
        $expectedJson = '[{"day":"Mon","intervals":[{"from":"09:00","to":"19:00"}]},'
            . '{"day":"Sat","intervals":[{"from":"09:00","to":"12:00"},{"from":"13:00","to":"14:00"}]}]';

        $statement = $this->createMock(PDOStatement::class);
        $statement->expects($this->once())
            ->method('execute')
            ->with($this->callback(
                static fn (array $params): bool => $params[':openingHours'] === $expectedJson,
            ));

        $pdo = $this->createStub(PDO::class);
        $pdo->method('prepare')
            ->willReturn($statement);

        $repository = new PdoPickupPointRepository($pdo);

        $repository->upsert([$this->createPickupPoint('10007', $openingHours)]);
    }

    public function testUpsertWithEmptyArrayExecutesNoStatement(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects($this->never())
            ->method('execute');

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($statement);
        $pdo->expects($this->once())
            ->method('beginTransaction');
        $pdo->expects($this->once())
            ->method('commit');
        $pdo->expects($this->never())
            ->method('rollBack');

        $repository = new PdoPickupPointRepository($pdo);

        $repository->upsert([]);
    }

    public function testUpsertRollsBackAndRethrowsWhenAStatementFails(): void
    {
        $failure = new RuntimeException('constraint violation');
        $statement = $this->createStub(PDOStatement::class);
        $statement->method('execute')
            ->willThrowException($failure);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($statement);
        $pdo->expects($this->once())
            ->method('beginTransaction');
        $pdo->expects($this->once())
            ->method('rollBack');
        $pdo->expects($this->never())
            ->method('commit');

        $this->expectExceptionObject($failure);

        $repository = new PdoPickupPointRepository($pdo);

        $repository->upsert([$this->createPickupPoint('10007')]);
    }

    public function testMarkStaleExecutesTheUpdateWithBoundParameters(): void
    {
        $statement = $this->createMock(PDOStatement::class);
        $statement->expects($this->once())
            ->method('execute')
            ->with([
                ':carrier'         => Carrier::BALIKOVNA->value,
                ':country'         => Country::CZ->value,
                ':importStartedAt' => '2024-01-02 03:04:05',
            ]);

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('prepare')
            ->with($this->logicalAnd(
                $this->stringContains('UPDATE pickup_points'),
                $this->stringContains("status NOT IN ('temporarily_unavailable', 'closed', 'terminated')"),
            ))
            ->willReturn($statement);

        $repository = new PdoPickupPointRepository($pdo);

        $repository->markStaleAsTemporarilyUnavailable(
            Carrier::BALIKOVNA,
            Country::CZ,
            new DateTimeImmutable('2024-01-02 03:04:05'),
        );
    }

    private function createPickupPoint(
        string $externalId,
        ?OpeningHours $openingHours = null,
    ): PickupPoint {
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
            openingHours: $openingHours,
        );
    }
}
