<?php

declare(strict_types=1);

namespace App\Import\ImportProgressReporter;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleProgressReporter implements ImportProgressReporter
{
    private ProgressBar $bar;
    private int $total = 0;

    public function __construct(
        private readonly OutputInterface $output,
        private readonly string $carrierName,
    ) {
    }

    public function preparing(): void
    {
        $this->output->writeln(sprintf('Importing pickup points from %s...', $this->carrierName));
    }

    public function start(int $total): void
    {
        $this->total = $total;
        $this->bar = new ProgressBar($this->output, $total);
        $this->bar->start();
    }

    public function setProgress(int $imported): void
    {
        $this->bar->setProgress($imported);
    }

    public function finish(): void
    {
        $this->bar->finish();
        $this->output->writeln('');
        $this->output->writeln(sprintf('Imported %d pickup points from %s.', $this->total, $this->carrierName));
    }
}
