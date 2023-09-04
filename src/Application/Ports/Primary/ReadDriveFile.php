<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Primary;

use ddziaduch\PenaltyPoints\Application\ReadDriveFileService;

interface ReadDriveFile
{
    public function isDrivingLicenseValid(string $drivingLicenceNumber): bool;

    public function sumOfValidPenaltyPoints(string $drivingLicenceNumber): int;
}
