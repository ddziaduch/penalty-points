<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Application;

use ddziaduch\PenaltyPoints\Adapters\Secondary\InMemoryDriverFiles;
use ddziaduch\PenaltyPoints\Application\PoliceOfficerService;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\StoreDriverFile;
use ddziaduch\PenaltyPoints\Domain\DriverFile;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \ddziaduch\PenaltyPoints\Application\PoliceOfficerService
 *
 * @internal
 */
final class PoliceOfficerServiceIntegrationTest extends KernelTestCase
{
    private const DRIVER_LICENSE_NUMBER = 'lorem-ipsum';

    public function testImposesPenaltyAndStoresIt(): void
    {
        $now = new \DateTimeImmutable();

        $driverFile = new DriverFile(
            self::DRIVER_LICENSE_NUMBER,
            $now->modify('-24 months'),
        );
        $storeDriverFile = $this->storeDriverFile();
        $storeDriverFile->store($driverFile);

        $service = $this->getService();
        $service->imposePenalty($driverFile->licenseNumber, 'CS', 123, 2, false);
        $service->imposePenalty($driverFile->licenseNumber, 'CS', 456, 4, false);
        $service->imposePenalty($driverFile->licenseNumber, 'CS', 789, 6, true);

        $driverFileFromStorage = $this->getDriverFile()->get(self::DRIVER_LICENSE_NUMBER);
        $driverFileFromStorage->payPenalty('CS', 123, $now);
        $driverFileFromStorage->payPenalty('CS', 456, $now);

        $this->expectException(\DomainException::class);
        $driverFileFromStorage->payPenalty('CS', 789, $now);
    }

    private function getService(): PoliceOfficerService
    {
        return self::getContainer()->get(PoliceOfficerService::class);
    }

    private function storeDriverFile(): InMemoryDriverFiles
    {
        return self::getContainer()->get(StoreDriverFile::class);
    }

    private function getDriverFile(): InMemoryDriverFiles
    {
        return self::getContainer()->get(GetDriverFile::class);
    }
}
