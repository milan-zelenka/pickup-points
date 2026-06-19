<?php

declare(strict_types=1);

namespace App\Import;

use App\Import\ImportProgressReporter\ImportProgressReporter;
use App\PickupPoint\Carrier;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ImportPickupPointsCommandTest extends TestCase
{
    public function testRejectsUnknownCarrier(): void
    {
        $importer = $this->createMock(Importer::class);
        $importer->expects($this->never())
            ->method('import');
        $tester = new CommandTester(new ImportPickupPointsCommand($importer));

        $exitCode = $tester->execute(['carrier' => 'fake-carrier']);

        Assert::assertSame(Command::INVALID, $exitCode);
        Assert::assertStringContainsString('Invalid carrier "fake-carrier"', $tester->getDisplay());
    }

    public function testImportsKnownCarrier(): void
    {
        $importer = $this->createMock(Importer::class);
        $importer->expects($this->once())
            ->method('import')
            ->with(
                Carrier::BALIKOVNA,
                $this->isInstanceOf(ImportProgressReporter::class),
            );
        $tester = new CommandTester(new ImportPickupPointsCommand($importer));

        $exitCode = $tester->execute(['carrier' => Carrier::BALIKOVNA->value]);

        Assert::assertSame(Command::SUCCESS, $exitCode);
    }
}
