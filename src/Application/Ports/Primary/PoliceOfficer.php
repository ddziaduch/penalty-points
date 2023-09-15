<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Primary;

interface PoliceOfficer
{
    public function imposePenalty(
        string $driverLicenseNumber,
        bool $isPaid,
        int $numberOfPoints,
    ): void;
}
