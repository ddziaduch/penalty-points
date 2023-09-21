<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Application;

use ddziaduch\PenaltyPoints\Adapters\Secondary\FixedClock;
use ddziaduch\PenaltyPoints\Adapters\Secondary\InMemoryDriverFiles;
use ddziaduch\PenaltyPoints\Application\DriverFileDoesNotExist;
use ddziaduch\PenaltyPoints\Application\PayPenaltyService;
use ddziaduch\PenaltyPoints\Domain\DriverFile;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ddziaduch\PenaltyPoints\Application\PayPenaltyService
 *
 * @internal
 */
class PayPenaltyServiceTest extends TestCase
{
    private \DateTimeImmutable $now;
    private DriverFile $driverFile;
    private PayPenaltyService $service;
    private InMemoryDriverFiles $driverFiles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = new \DateTimeImmutable();
        $this->driverFile = new DriverFile(
            licenseNumber: '12345',
            examPassedAt: (new \DateTimeImmutable())->modify('-24 months'),
        );

        $clock = new FixedClock($this->now);

        $this->driverFiles = new InMemoryDriverFiles();
        $this->driverFiles->store($this->driverFile);

        $this->service = new PayPenaltyService($clock, $this->driverFiles, $this->driverFiles);
    }

    public function testPayingPenalty(): void
    {
        $series = 'CD';
        $number = 12345;

        $this->driverFile->imposePenalty(
            series: $series,
            number: $number,
            occurredAt: $this->now,
            numberOfPoints: 10,
            isPaidOnSpot: false,
        );

        $this->service->pay($this->driverFile->licenseNumber, $series, $number);

        $driverFileFromStorage = $this->driverFiles->get($this->driverFile->licenseNumber);

        $this->expectException(\DomainException::class);
        $driverFileFromStorage->payPenalty($series, $number, $this->now);
    }

    public function testPenaltyDoesNotExist(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $this->service->pay($this->driverFile->licenseNumber, 'XX', 9999);
    }

    public function testDriverFileDoesNotExist(): void
    {
        $this->expectException(DriverFileDoesNotExist::class);
        $this->service->pay('98765', 'XX', 9999);
    }
}
