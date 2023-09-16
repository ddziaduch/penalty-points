<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Primary;

interface PoliceOfficer
{
    /**
     * @param int $number *
     * @param string $series
     *
* @throws \DomainException
     */
    public function imposeUnpaidPenalty(
        string $driverLicenseNumber,
        string $penaltySeries,
        int $penaltyNumber,
        int $numberOfPenaltyPoints,
    ): void;

    public function imposePenaltyPaidOnSpot(
        string $driverLicenseNumber,
        int $penaltyNumber,
        string $penaltySeries,
        int $numberOfPenaltyPoints
    ): void;
}
