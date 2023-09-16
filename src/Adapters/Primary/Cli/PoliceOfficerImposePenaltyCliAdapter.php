<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Adapters\Primary\Cli;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\PoliceOfficer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'police-officer:impose-penalty', description: 'Allows to impose a new penalty to the driver')]
final class PoliceOfficerImposePenaltyCliAdapter extends Command
{
    public function __construct(
        private readonly PoliceOfficer $policeOfficer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('driverLicenseNumber', InputArgument::REQUIRED, 'The license number of the driver');
        $this->addArgument('penaltySeries', InputArgument::REQUIRED, 'The series of the penalty');
        $this->addArgument('penaltyNumber', InputArgument::REQUIRED, 'The number of the penalty');
        $this->addArgument('numberOfPenaltyPoints', InputArgument::REQUIRED, 'The number of penalty points');
        $this->addArgument('isPaidOnSpot', InputArgument::REQUIRED, 'Whether the penalty is paid on spot');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $numberOfPoints = $input->getArgument('numberOfPenaltyPoints');
        $penaltyNumber = $input->getArgument('penaltyNumber');
        $isPaidOnSpot = $input->getArgument('isPaidOnSpot');

        if (!is_numeric($numberOfPoints) || !is_int($numberOfPoints + 0) ) {
            $output->writeln('The number of penalty points needs to be an integer');

            return self::INVALID;
        }

        if (!is_numeric($penaltyNumber) || !is_int($penaltyNumber + 0) ) {
            $output->writeln('The penalty number needs to be an integer');

            return self::INVALID;
        }

        if ($isPaidOnSpot !== '0' && $isPaidOnSpot !== '1') {
            $output->writeln('Is paid on spot must be either 1 or 0');

            return self::INVALID;
        }

        try {
            $this->policeOfficer->imposePenalty(
                $input->getArgument('driverLicenseNumber'),
                $input->getArgument('penaltySeries'),
                (int) $penaltyNumber,
                (int) $numberOfPoints,
                (bool) $isPaidOnSpot,
            );
        } catch (\DomainException | \OutOfBoundsException $exception) {
            $output->writeln($exception->getMessage());

            return self::FAILURE;
        }

        $output->writeln('Penalty imposed');

        return self::SUCCESS;
    }
}
