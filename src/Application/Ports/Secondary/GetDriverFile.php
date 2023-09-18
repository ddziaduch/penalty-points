<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Secondary;

use ddziaduch\PenaltyPoints\Domain\DriverFile;

interface GetDriverFile
{
    /** @throws \OutOfBoundsException */
    public function get(string $drivingLicenceNumber): DriverFile;
}
