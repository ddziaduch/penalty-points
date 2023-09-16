<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\PoliceOfficer;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\StoreDriverFile;
use Psr\Clock\ClockInterface;

final readonly class PoliceOfficerService implements PoliceOfficer
{
    public function __construct(
        private ClockInterface $clock,
        private GetDriverFile $getDriverFile,
        private StoreDriverFile $storeDriverFile,
    ) {}

    public function imposeUnpaidPenalty(
        string $driverLicenseNumber,
        string $penaltySeries,
        int $penaltyNumber,
        int $numberOfPenaltyPoints,
    ): void {
        $now = $this->clock->now();
        $driverFile = $this->getDriverFile->get($driverLicenseNumber);

        $driverFile->imposeUnpaidPenalty(
            series: $penaltySeries,
            number: $penaltyNumber,
            occurredAt: $now,
            numberOfPoints: $numberOfPenaltyPoints,
        );

        $this->storeDriverFile->store($driverFile);
    }

    public function imposePenaltyPaidOnSpot(
        string $driverLicenseNumber,
        int $penaltyNumber,
        string $penaltySeries,
        int $numberOfPenaltyPoints,
    ): void {
        $now = $this->clock->now();
        $driverFile = $this->getDriverFile->get($driverLicenseNumber);

        $driverFile->imposePenaltyPaidOnSpot(
            series: $penaltySeries,
            number: $penaltyNumber,
            occurredAt: $now,
            numberOfPoints: $numberOfPenaltyPoints,
        );

        $this->storeDriverFile->store($driverFile);
    }
}
