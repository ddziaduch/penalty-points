<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Application;

use ddziaduch\PenaltyPoints\Adapters\Secondary\FixedClock;
use ddziaduch\PenaltyPoints\Adapters\Secondary\InMemoryDriverFiles;
use ddziaduch\PenaltyPoints\Application\ImposePenaltyService;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\StoreDriverFile;
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
            'lorem-ipsum',
            $now->modify('-24 months'),
        );

        $clock = new FixedClock($now);

        $getDriverFile = self::createStub(GetDriverFile::class);
        $getDriverFile->method('get')->willReturn($driverFile);

        $storeDriverFile = $this->createMock(StoreDriverFile::class);
        $storeDriverFile->expects(self::exactly(3))->method('store')->with($driverFile);

        $imposePenaltyService = new ImposePenaltyService(
            $clock,
            $getDriverFile,
            $storeDriverFile,
        );
        $imposePenaltyService->impose(
            driverLicenseNumber: $driverFile->licenseNumber,
            penaltySeries: 'CS',
            penaltyNumber: 123,
            numberOfPenaltyPoints: 10,
            isPaidOnSpot: false,
        );
        $imposePenaltyService->impose(
            driverLicenseNumber: $driverFile->licenseNumber,
            penaltySeries: 'CS',
            penaltyNumber: 456,
            numberOfPenaltyPoints: 10,
            isPaidOnSpot: true,
        );

        $this->expectException(\DomainException::class); // it should store the penalty and throw the exception
        $imposePenaltyService->impose(
            driverLicenseNumber: $driverFile->licenseNumber,
            penaltySeries: 'CS',
            penaltyNumber: 789,
            numberOfPenaltyPoints: 10,
            isPaidOnSpot: false,
        );
    }

    public function testDriverFileDoesNotExist(): void
    {
        $getDriverFile = self::createStub(GetDriverFile::class);
        $getDriverFile->method('get')->willThrowException(new \OutOfBoundsException());

        $imposePenaltyService = new ImposePenaltyService(
            new FixedClock(new \DateTimeImmutable()),
            $getDriverFile,
            self::createStub(StoreDriverFile::class),
        );

        $this->expectException(\OutOfBoundsException::class);
        $imposePenaltyService->impose(
            driverLicenseNumber: 'xyz123',
            penaltySeries: 'BA',
            penaltyNumber: 999,
            numberOfPenaltyPoints: 10,
            isPaidOnSpot: true,
        );
    }
}
