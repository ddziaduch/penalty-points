<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Application;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\ImposePenalty;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\StoreDriverFile;
use ddziaduch\PenaltyPoints\Domain\Penalty;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final readonly class ImposePenaltyService implements ImposePenalty
{
    public function __construct(
        private ClockInterface $clock,
        private GetDriverFile $getDriverFile,
        private EventDispatcherInterface $eventDispatcher,
        private StoreDriverFile $storeDriverFile,
    ) {}

    public function impose(
        string $driverLicenseNumber,
        int $numberOfPoints,
    ): void {
        $driverFile = $this->getDriverFile->get($driverLicenseNumber);
        $now = $this->clock->now();
        $penalty = new Penalty($now, $numberOfPoints);

        $driverFile->imposePenalty($penalty);

        $events = $driverFile->dumpEvents();
        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        $this->storeDriverFile->store($driverFile);
    }
}
