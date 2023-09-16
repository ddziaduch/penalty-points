<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Application;

use ddziaduch\PenaltyPoints\Adapters\Secondary\InMemoryDriverFiles;
use ddziaduch\PenaltyPoints\Application\Ports\Primary\PoliceOfficer;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\GetDriverFile;
use ddziaduch\PenaltyPoints\Application\Ports\Secondary\StoreDriverFile;
use ddziaduch\PenaltyPoints\Domain\DriverFile;
use Psr\Clock\ClockInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/** @covers \ddziaduch\PenaltyPoints\Application\PoliceOfficerService */
final class ImposePenaltyServiceIntegrationTest extends KernelTestCase
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
        $service->imposePenalty($driverFile->licenseNumber, 'CS', 12345, 10, false);
        $service->imposePenalty($driverFile->licenseNumber, 'CS', 12345, 10, false);
        $service->imposePenalty($driverFile->licenseNumber, 'CS', 12345, 10, false);

        $driverFileFromStorage = $this->getDriverFile()->get(self::DRIVER_LICENSE_NUMBER);
        self::assertFalse($driverFileFromStorage->isDrivingLicenseValid($now));
    }

    private function getService(): PoliceOfficer
    {
        return self::getContainer()->get(PoliceOfficer::class);
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
