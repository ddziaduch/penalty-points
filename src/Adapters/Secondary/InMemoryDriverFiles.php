<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Adapters\Secondary;

use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\StoreDriverFile;
use ddziaduch\PenaltyPoints\Domain\DriverFile;

final class InMemoryDriverFiles implements GetDriverFile, StoreDriverFile
{
    /** @var array<string, DriverFile> */
    private array $driverFiles = [];

    public function get(string $drivingLicenceNumber): DriverFile
    {
        if (!array_key_exists($drivingLicenceNumber, $this->driverFiles)) {
            // TODO: proper exception
            throw new \OutOfBoundsException('The driver file does not exist');
        }

        return $this->driverFiles[$drivingLicenceNumber];
    }

    public function store(DriverFile $driverFile): void
    {
        $this->driverFiles[$driverFile->licenseNumber] = $driverFile;
    }
}
