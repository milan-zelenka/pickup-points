<?php

declare(strict_types=1);

namespace App\Import\ImportProgressReporter;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

class ConsoleProgressReporterTest extends TestCase
{
    public function testPreparingOutputsCarrierMessage(): void
    {
        $output = new BufferedOutput();
        $reporter = new ConsoleProgressReporter($output, 'Balíkovna');

        $reporter->preparing();

        Assert::assertStringContainsString('Importing pickup points from Balíkovna...', $output->fetch());
    }

    public function testFinishOutputsImportedCountAndCarrierName(): void
    {
        $output = new BufferedOutput();
        $reporter = new ConsoleProgressReporter($output, 'Balíkovna');

        $reporter->start(42);
        $reporter->finish();

        Assert::assertStringContainsString('Imported 42 pickup points from Balíkovna.', $output->fetch());
    }

    public function testSetProgressDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();

        $output = new BufferedOutput();
        $reporter = new ConsoleProgressReporter($output, 'Balíkovna');
        $reporter->start(10);
        $reporter->setProgress(5);
    }
}
