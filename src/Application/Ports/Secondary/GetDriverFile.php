<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Secondary;

use ddziaduch\PenaltyPoints\Application\DriverFileDoesNotExist;
use ddziaduch\PenaltyPoints\Domain\DriverFile;

interface GetDriverFile
{
    /** @throws DriverFileDoesNotExist */
    public function get(string $licenceNumber): DriverFile;
}
