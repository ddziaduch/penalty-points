<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Application;

use ddziaduch\PenaltyPoints\Adapters\Secondary\InMemoryDriverFiles;
use ddziaduch\PenaltyPoints\Application\Ports\Primary\PoliceOfficer;
use ddziaduch\PenaltyPoints\Domain\DriverFile;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/** @covers \ddziaduch\PenaltyPoints\Application\PoliceOfficerService */
final class ImposePenaltyServiceIntegrationTest extends KernelTestCase
{
    public function testImposesPenaltyAndDispatchesEvents(): void
    {
        $this->markTestIncomplete('fix me');
        $eventDispatcher = $this->getEventDispatcher();
        $eventSubscriber = new class implements EventSubscriberInterface {
            /** @var object[] */
            public array $events;

            public static function getSubscribedEvents(): array
            {
                return [
                    PenaltyImposed::class => 'onEvent',
                    DrivingLicenseNoLongerValid::class => 'onEvent',
                ];
            }

            public function onEvent(object $event): void
            {
                $this->events[] = $event;
            }
        };
        $eventDispatcher->addSubscriber($eventSubscriber);

        $now = new \DateTimeImmutable();

        $driverFile = new DriverFile(
            '11111/22/3333',
            $now->modify('-24 months'),
        );
        $driverFiles = $this->getDriverFiles();
        $driverFiles->store($driverFile);

        $service = $this->getService();
        $service->imposePenalty($driverFile->licenseNumber, 'CS', 12345, 10, false);
        $service->imposePenalty($driverFile->licenseNumber, 'CS', 12345, 10, false);
        $service->imposePenalty($driverFile->licenseNumber, 'CS', 12345, 10, false);

        self::assertCount(4, $eventSubscriber->events);
        self::assertFalse($driverFile->isDrivingLicenseValid($now));
    }

    private function getEventDispatcher(): EventDispatcherInterface
    {
        $eventDispatcher = self::getContainer()->get('event_dispatcher');
        assert($eventDispatcher instanceof EventDispatcherInterface);

        return $eventDispatcher;
    }

    private function getService(): PoliceOfficer
    {
        $service = self::getContainer()->get(PoliceOfficer::class);
        assert($service instanceof PoliceOfficer);

        return $service;
    }

    private function getDriverFiles(): InMemoryDriverFiles
    {
        $driverFiles = self::getContainer()->get(InMemoryDriverFiles::class);
        assert($driverFiles instanceof InMemoryDriverFiles);

        return $driverFiles;
    }
}
