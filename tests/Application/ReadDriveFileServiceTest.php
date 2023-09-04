<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Application;

use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Application\ReadDriveFileService;
use ddziaduch\PenaltyPoints\Domain\DriverFile;
use ddziaduch\PenaltyPoints\Domain\Penalty;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

class ReadDriveFileServiceTest extends TestCase
{
    private \DateTimeImmutable $now;
    private DriverFile $driverFile;

    public function testSumOfValidPenaltyPoints(): void
    {
        self::assertSame(
            $this->driverFile->sumOfValidPenaltyPoints($this->now),
            $this->readDriverFileService()->sumOfValidPenaltyPoints('123456789'),
        );
    }

    public function testIsDrivingLicenseValid(): void
    {
        self::assertSame(
            $this->driverFile->isDrivingLicenseValid($this->now),
            $this->readDriverFileService()->isDrivingLicenseValid('123456789'),
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = new \DateTimeImmutable();

        $this->driverFile = new DriverFile($this->now->modify('-24 months'));
        $this->driverFile->imposePenalty(new Penalty($this->now->modify('-6 months'), 10));
    }

    private function readDriverFileService(): ReadDriveFileService
    {
        $clock = self::createStub(ClockInterface::class);
        $clock->method('now')->willReturn($this->now);

        $getDriverFilePort = self::createStub(GetDriverFile::class);
        $getDriverFilePort->method('get')->willReturn($this->driverFile);

        return new ReadDriveFileService($clock, $getDriverFilePort);
    }
}
