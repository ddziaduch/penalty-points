<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Primary;

use ddziaduch\PenaltyPoints\Domain\Penalty;

interface WriteDriverFile
{
    public function imposePenalty(
        string $driverLicenseNumber,
        int $numberOfPoints,
    ): void;
}
