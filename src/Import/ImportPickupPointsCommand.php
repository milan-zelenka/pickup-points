<?php

declare(strict_types=1);

namespace App\Import;

use App\Import\ImportProgressReporter\ConsoleProgressReporter;
use App\PickupPoint\Carrier;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'pickup-points:import',
    description: 'Import pickup points from the given carrier',
)]
class ImportPickupPointsCommand extends Command
{
    public function __construct(private readonly Importer $importer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'carrier',
            InputArgument::REQUIRED,
            sprintf('Carrier key (one of: %s)', implode(', ', Carrier::getCarrierNames())),
            null,
            Carrier::getCarrierNames(),
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $carrierName = $input->getArgument('carrier');

        if (!is_string($carrierName)) {
            return Command::INVALID;
        }

        $carrier = Carrier::tryFrom($carrierName);

        if ($carrier === null) {
            $output->writeln(sprintf(
                '<error>Invalid carrier "%s". Expected one of: %s.</error>',
                $carrierName,
                implode(', ', Carrier::getCarrierNames()),
            ));

            return Command::INVALID;
        }

        $this->importer->import(
            $carrier,
            new ConsoleProgressReporter($output, $carrier->value),
        );

        return Command::SUCCESS;
    }
}
