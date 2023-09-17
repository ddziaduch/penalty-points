<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Primary;

interface Driver
{
    /** @throws \OutOfBoundsException */
    public function payPenalty(string $drivingLicenceNumber, string $series, int $number): void;
}
