<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Application;

use ddziaduch\PenaltyPoints\Application\ImposePenaltyService;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\StoreDriverFile;
use ddziaduch\PenaltyPoints\Domain\DriverFile;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \ddziaduch\PenaltyPoints\Application\ImposePenaltyService
 *
 * @internal
 */
final class ImposePenaltyServiceIntegrationTest extends KernelTestCase
{
    private const DRIVER_LICENSE_NUMBER = 'lorem-ipsum';

    public function testImposesPenaltyAndStoresIt(): void
    {
        $container = self::getContainer();
        $now = new \DateTimeImmutable();

        $driverFile = new DriverFile(
            self::DRIVER_LICENSE_NUMBER,
            $now->modify('-24 months'),
        );

        $storeDriverFile = $container->get(StoreDriverFile::class);
        $storeDriverFile->store($driverFile);

        $imposePenaltyService = $container->get(ImposePenaltyService::class);
        $imposePenaltyService->imposePenalty(
            driverLicenseNumber: $driverFile->licenseNumber,
            penaltySeries: 'CS',
            penaltyNumber: 123,
            numberOfPenaltyPoints: 2,
            isPaidOnSpot: false,
        );
        $imposePenaltyService->imposePenalty(
            driverLicenseNumber: $driverFile->licenseNumber,
            penaltySeries: 'CS',
            penaltyNumber: 456,
            numberOfPenaltyPoints: 4,
            isPaidOnSpot: false,
        );
        $imposePenaltyService->imposePenalty(
            driverLicenseNumber: $driverFile->licenseNumber,
            penaltySeries: 'CS',
            penaltyNumber: 789,
            numberOfPenaltyPoints: 6,
            isPaidOnSpot: true,
        );

        $driverFileFromStorage = $container->get(GetDriverFile::class)->get(self::DRIVER_LICENSE_NUMBER);
        $driverFileFromStorage->payPenalty('CS', 123, $now);
        $driverFileFromStorage->payPenalty('CS', 456, $now);

        $this->expectException(\DomainException::class);
        $driverFileFromStorage->payPenalty('CS', 789, $now);
    }

    public function testDriverFileDoesNotExist(): void
    {
        $imposePenaltyService = self::getContainer()->get(ImposePenaltyService::class);

        $this->expectException(\OutOfBoundsException::class);
        $imposePenaltyService->imposePenalty(
            driverLicenseNumber: 'xyz123',
            penaltySeries: 'BA',
            penaltyNumber: 999,
            numberOfPenaltyPoints: 10,
            isPaidOnSpot: true,
        );
    }
}
