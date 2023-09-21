<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\PayPenalty;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\StoreDriverFile;
use Psr\Clock\ClockInterface;

final readonly class PayPenaltyService implements PayPenalty
{
    public function __construct(
        private ClockInterface $clock,
        private GetDriverFile $getDriverFile,
        private StoreDriverFile $storeDriverFile,
    ) {}

    public function pay(string $drivingLicenceNumber, string $series, int $number): void
    {
        $driverFile = $this->getDriverFile->get($drivingLicenceNumber);
        $now = $this->clock->now();
        $driverFile->payPenalty($series, $number, $now);
        $this->storeDriverFile->store($driverFile);
    }
}
