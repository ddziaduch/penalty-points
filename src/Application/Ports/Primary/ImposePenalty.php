<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Primary;

use ddziaduch\PenaltyPoints\Domain\Penalty;

interface ImposePenalty
{
    public function impose(
        string $driverLicenseNumber,
        int $numberOfPoints,
    ): void;
}
