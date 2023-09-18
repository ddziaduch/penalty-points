<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Primary;

interface PayPenalty
{
    /** @throws \OutOfBoundsException */
    public function pay(string $drivingLicenceNumber, string $series, int $number): void;
}
