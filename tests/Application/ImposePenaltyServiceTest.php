<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Application;

use ddziaduch\PenaltyPoints\Adapters\Secondary\FixedClock;
use ddziaduch\PenaltyPoints\Adapters\Secondary\InMemoryDriverFiles;
use ddziaduch\PenaltyPoints\Application\DriverFileDoesNotExist;
use ddziaduch\PenaltyPoints\Application\ImposePenaltyService;
use ddziaduch\PenaltyPoints\Domain\DriverFile;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ddziaduch\PenaltyPoints\Application\ImposePenaltyService
 *
 * @internal
 */
final class ImposePenaltyServiceTest extends TestCase
{
    public function testImposesPenaltyAndStoresIt(): void
    {
        $now = new \DateTimeImmutable();

        $driverFile = new DriverFile(
            licenseNumber: 'lorem-ipsum',
            examPassedAt: $now->modify('-24 months'),
        );

        $clock = new FixedClock($now);

        $driverFiles = new InMemoryDriverFiles();
        $driverFiles->store($driverFile);

        $service = new ImposePenaltyService(
            $clock,
            $driverFiles,
            $driverFiles,
        );

        $service->impose(
            driverLicenseNumber: $driverFile->licenseNumber,
            penaltySeries: 'CS',
            penaltyNumber: 123,
            numberOfPenaltyPoints: 10,
            isPaidOnSpot: false,
        );
        $service->impose(
            driverLicenseNumber: $driverFile->licenseNumber,
            penaltySeries: 'CS',
            penaltyNumber: 456,
            numberOfPenaltyPoints: 10,
            isPaidOnSpot: true,
        );

        $this->expectException(\DomainException::class);

        try {
            $service->impose(
                driverLicenseNumber: $driverFile->licenseNumber,
                penaltySeries: 'CS',
                penaltyNumber: 789,
                numberOfPenaltyPoints: 10,
                isPaidOnSpot: false,
            );
        } finally {
            self::assertFalse($driverFile->isDrivingLicenseValid($now));
            self::assertSame(30, $driverFile->sumOfValidPenaltyPoints($now));
        }
    }

    public function testDriverFileDoesNotExist(): void
    {
        $driverFiles = new InMemoryDriverFiles();

        $service = new ImposePenaltyService(
            new FixedClock(new \DateTimeImmutable()),
            $driverFiles,
            $driverFiles,
        );

        $this->expectException(DriverFileDoesNotExist::class);
        $service->impose(
            driverLicenseNumber: 'xyz123',
            penaltySeries: 'BA',
            penaltyNumber: 999,
            numberOfPenaltyPoints: 10,
            isPaidOnSpot: true,
        );
    }
}
