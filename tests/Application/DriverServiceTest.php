<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Application;

use ddziaduch\PenaltyPoints\Adapters\Secondary\FixedClock;
use ddziaduch\PenaltyPoints\Adapters\Secondary\InMemoryDriverFiles;
use ddziaduch\PenaltyPoints\Application\DriverService;
use ddziaduch\PenaltyPoints\Domain\DriverFile;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ddziaduch\PenaltyPoints\Application\DriverService
 *
 * @internal
 */
class DriverServiceTest extends TestCase
{
    private \DateTimeImmutable $now;
    private DriverFile $driverFile;
    private DriverService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = new \DateTimeImmutable();
        $this->driverFile = new DriverFile(
            licenseNumber: '12345',
            examPassedAt: (new \DateTimeImmutable())->modify('-24 months'),
        );

        $clock = new FixedClock($this->now);

        $driverFiles = new InMemoryDriverFiles($clock);
        $driverFiles->store($this->driverFile);

        $this->service = new DriverService($clock, $driverFiles);
    }

    public function testPayingPenalty(): void
    {
        $series = 'CD';
        $number = 12345;

        $this->driverFile->imposePenalty(
            series: $series,
            number: $number,
            occurredAt: new \DateTimeImmutable(),
            numberOfPoints: 10,
            isPaidOnSpot: true,
        );

        $this->expectException(\DomainException::class);
        $this->service->payPenalty($this->driverFile->licenseNumber, $series, $number);
    }
}
