<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\ReadDriveFile;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use Psr\Clock\ClockInterface;

final readonly class ReadDriveFileService implements ReadDriveFile
{
    public function __construct(
        private ClockInterface $clock,
        private GetDriverFile $getDriverFile,
    ) {}

    public function isDrivingLicenseValid(string $drivingLicenceNumber): bool
    {
        $driverFile = $this->getDriverFile->get($drivingLicenceNumber);
        $now = $this->clock->now();

        return $driverFile->isDrivingLicenseValid($now);
    }

    public function sumOfValidPenaltyPoints(string $drivingLicenceNumber): int
    {
        $driverFile = $this->getDriverFile->get($drivingLicenceNumber);
        $now = $this->clock->now();

        return $driverFile->sumOfValidPenaltyPoints($now);
    }
}
