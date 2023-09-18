<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application;

use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\StoreDriverFile;
use Psr\Clock\ClockInterface;

final readonly class ImposePenaltyService
{
    public function __construct(
        private ClockInterface $clock,
        private GetDriverFile $getDriverFile,
        private StoreDriverFile $storeDriverFile,
    ) {}

    public function imposePenalty(
        string $driverLicenseNumber,
        string $penaltySeries,
        int $penaltyNumber,
        int $numberOfPenaltyPoints,
        bool $isPaidOnSpot,
    ): void {
        $now = $this->clock->now();
        $driverFile = $this->getDriverFile->get($driverLicenseNumber);

        try {
            $driverFile->imposePenalty(
                series: $penaltySeries,
                number: $penaltyNumber,
                occurredAt: $now,
                numberOfPoints: $numberOfPenaltyPoints,
                isPaidOnSpot: $isPaidOnSpot,
            );
        } finally {
            $this->storeDriverFile->store($driverFile);
        }
    }
}
