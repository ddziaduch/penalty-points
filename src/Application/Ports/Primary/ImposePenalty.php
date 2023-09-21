<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Primary;

use ddziaduch\PenaltyPoints\Application\DriverFileDoesNotExist;
use ddziaduch\PenaltyPoints\Domain\PenaltyImposedButDriversLicenseIsNotValidAnymore;

interface ImposePenalty
{
    /**
     * @throws DriverFileDoesNotExist
     * @throws PenaltyImposedButDriversLicenseIsNotValidAnymore
     */
    public function impose(
        string $driverLicenseNumber,
        string $penaltySeries,
        int $penaltyNumber,
        int $numberOfPenaltyPoints,
        bool $isPaidOnSpot,
    ): void;
}
