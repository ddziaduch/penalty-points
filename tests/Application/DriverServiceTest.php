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
        $this->driverFile = InMemoryDriverFiles::newbieDriverFile($this->now);

        $clock = new FixedClock($this->now);

        $getDriverFile = new InMemoryDriverFiles($clock);
        $getDriverFile->store($this->driverFile);

        $this->service = new DriverService($clock, $getDriverFile);
    }

    public function testSumOfValidPenaltyPoints(): void
    {
        self::assertSame(
            $this->driverFile->sumOfValidPenaltyPoints($this->now),
            $this->service->sumOfValidPenaltyPoints($this->driverFile->licenseNumber),
        );
    }

    public function testIsDrivingLicenseValid(): void
    {
        self::assertSame(
            $this->driverFile->isDrivingLicenseValid($this->now),
            $this->service->isDrivingLicenseValid($this->driverFile->licenseNumber),
        );
    }
}
