<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Adapters\Primary;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\PoliceOfficer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'police-officer:impose-unpaid-penalty', description: 'Allows to impose a new penalty to the driver')]
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
        $this->addArgument('isPaidOnSpot', InputArgument::REQUIRED, 'Whether the penalty is paid on spot');
        $this->addArgument('numberOfPenaltyPoints', InputArgument::REQUIRED, 'The number of penalty points');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $numberOfPoints = $input->getArgument('numberOfPenaltyPoints');
        $penaltyNumber = $input->getArgument('penaltyNumber');

        if (!is_numeric($numberOfPoints) || !is_int($numberOfPoints + 0) ) {
            $output->writeln('The number of penalty points needs to be an integer');

            return self::INVALID;
        }

        if (!is_numeric($penaltyNumber) || !is_int($penaltyNumber + 0) ) {
            $output->writeln('The penalty number needs to be an integer');

            return self::INVALID;
        }

        $this->policeOfficer->imposePenalty(
            $input->getArgument('driverLicenseNumber'),
            $input->getArgument('penaltySeries'),
            (int) $penaltyNumber,
            (int) $numberOfPoints,
            (bool) $input->getArgument('isPaidOnSpot'),
        );

        return self::SUCCESS;
    }
}
