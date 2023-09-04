<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application;

use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Domain\Penalty;
use Psr\Clock\ClockInterface;

final readonly class WriteDriverFileService implements Ports\Primary\WriteDriverFile
{
    public function __construct(
        private ClockInterface $clock,
        private GetDriverFile $getDriverFile,
    ) {}

    public function imposePenalty(
        string $driverLicenseNumber,
        int $numberOfPoints
    ): void {
        $driverFile = $this->getDriverFile->get($driverLicenseNumber);
        $now = $this->clock->now();
        $penalty = new Penalty($now, $numberOfPoints);
        $driverFile->imposePenalty($penalty);
        // TODO: raise domain event
    }
}
