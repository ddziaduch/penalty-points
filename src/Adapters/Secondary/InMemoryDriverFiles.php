<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Adapters\Secondary;

use ddziaduch\PenaltyPoints\Application\DriverFileDoesNotExist;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\StoreDriverFile;
use ddziaduch\PenaltyPoints\Domain\DriverFile;

final class InMemoryDriverFiles implements GetDriverFile, StoreDriverFile
{
    /** @var array<string, DriverFile> */
    private array $driverFiles = [];

    public function get(string $licenceNumber): DriverFile
    {
        if (!array_key_exists($licenceNumber, $this->driverFiles)) {
            throw new DriverFileDoesNotExist($licenceNumber);
        }

        return $this->driverFiles[$licenceNumber];
    }

    public function store(DriverFile $driverFile): void
    {
        $this->driverFiles[$driverFile->licenseNumber] = $driverFile;
    }
}
