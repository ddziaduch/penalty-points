<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Application;

use ddziaduch\PenaltyPoints\Adapters\Secondary\FixedClock;
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
            'lorem-ipsum',
            $now->modify('-24 months'),
        );

        $clock = new FixedClock($now);

        $driverFiles = new InMemoryDriverFiles();
        $driverFiles->store($driverFile);

        $imposePenaltyService = new ImposePenaltyService(
            $clock,
            $driverFiles,
            $driverFiles,
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

        $this->expectException(\DomainException::class);
        try {
            $imposePenaltyService->impose(
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

        $imposePenaltyService = new ImposePenaltyService(
            new FixedClock(new \DateTimeImmutable()),
            $driverFiles,
            $driverFiles,
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
