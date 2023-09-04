<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Secondary;

use ddziaduch\PenaltyPoints\Domain\DriverFile;

interface DriveFileRepository
{
    public function get(string $drivingLicenceNumber): DriverFile;
}
