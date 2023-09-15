<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Application;

use ddziaduch\PenaltyPoints\Adapters\Secondary\FixedClock;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Application\ReadDriveFileService;
use ddziaduch\PenaltyPoints\Domain\DriverFile;
use PHPUnit\Framework\TestCase;

/** @covers \ddziaduch\PenaltyPoints\Application\ReadDriveFileService */
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

        $this->driverFile = new DriverFile('12345', $this->now->modify('-24 months'));
        $this->driverFile->imposePenalty(
            'CS',
            12345,
            occurredAt: $this->now->modify('-6 months'),
            isPaid: false,
            numberOfPoints: 10,
        );
    }

    private function readDriverFileService(): ReadDriveFileService
    {
        $clock = new FixedClock($this->now);

        $getDriverFilePort = self::createStub(GetDriverFile::class);
        $getDriverFilePort->method('get')->willReturn($this->driverFile);

        return new ReadDriveFileService($clock, $getDriverFilePort);
    }
}
