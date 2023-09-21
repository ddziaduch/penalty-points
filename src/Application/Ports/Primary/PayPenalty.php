<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application\Ports\Primary;

use ddziaduch\PenaltyPoints\Application\DriverFileDoesNotExist;
use ddziaduch\PenaltyPoints\Domain\PenaltyAlreadyPaid;
use ddziaduch\PenaltyPoints\Domain\PenaltyDoesNotExist;

interface PayPenalty
{
    /**
     * @throws DriverFileDoesNotExist
     * @throws PenaltyDoesNotExist
     * @throws PenaltyAlreadyPaid
     */
    public function pay(string $drivingLicenceNumber, string $series, int $number): void;
}
