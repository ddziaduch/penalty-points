<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Primary;

interface PoliceOfficer
{
    /**
     * @param bool $isPaidOnSpot
     * @param int $number *
     * @param string $series
     *
* @throws \DomainException
     */
    public function imposePenalty(
        string $driverLicenseNumber,
        string $penaltySeries,
        int $penaltyNumber,
        int $numberOfPenaltyPoints,
        bool $isPaidOnSpot,
    ): void;
}
