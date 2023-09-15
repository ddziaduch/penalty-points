<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Primary;

interface Driver
{
    public function isDrivingLicenseValid(string $drivingLicenceNumber): bool;

    public function sumOfValidPenaltyPoints(string $drivingLicenceNumber): int;

    /** @throws \OutOfBoundsException */
    public function payPenalty(string $drivingLicenceNumber, string $series, int $number): void;
}
