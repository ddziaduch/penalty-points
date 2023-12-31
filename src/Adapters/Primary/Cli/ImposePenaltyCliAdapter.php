<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Adapters\Primary\Cli;

use ddziaduch\PenaltyPoints\Application\DriverFileDoesNotExist;
use ddziaduch\PenaltyPoints\Application\Ports\Primary\ImposePenalty;
use ddziaduch\PenaltyPoints\Domain\PenaltyImposedButDriversLicenseIsNotValidAnymore;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'police-officer:impose-penalty', description: 'Allows to impose a new penalty to the driver')]
final class ImposePenaltyCliAdapter extends Command
{
    public function __construct(
        private readonly ImposePenalty $imposePenalty,
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
        $driverLicenseNumber = $input->getArgument('driverLicenseNumber');
        $penaltySeries = $input->getArgument('penaltySeries');
        $penaltyNumber = $input->getArgument('penaltyNumber');
        $numberOfPoints = $input->getArgument('numberOfPenaltyPoints');
        $isPaidOnSpot = $input->getArgument('isPaidOnSpot');

        if (!is_string($driverLicenseNumber)) {
            $output->writeln('The driver license number needs to be a string');

            return self::INVALID;
        }

        if (!is_string($penaltySeries)) {
            $output->writeln('The driver license number needs to be a string');

            return self::INVALID;
        }

        if (!is_numeric($numberOfPoints) || !is_int($numberOfPoints + 0)) {
            $output->writeln('The number of penalty points needs to be an integer');

            return self::INVALID;
        }

        if (!is_numeric($penaltyNumber) || !is_int($penaltyNumber + 0)) {
            $output->writeln('The penalty number needs to be an integer');

            return self::INVALID;
        }

        if ('0' !== $isPaidOnSpot && '1' !== $isPaidOnSpot) {
            $output->writeln('Is paid on spot must be either 1 or 0');

            return self::INVALID;
        }

        try {
            $this->imposePenalty->impose(
                $driverLicenseNumber,
                $penaltySeries,
                (int) $penaltyNumber,
                (int) $numberOfPoints,
                (bool) $isPaidOnSpot,
            );
        } catch (DriverFileDoesNotExist|PenaltyImposedButDriversLicenseIsNotValidAnymore $exception) {
            $output->writeln($exception->getMessage());

            return self::FAILURE;
        }

        $output->writeln('Penalty imposed');

        return self::SUCCESS;
    }
}
