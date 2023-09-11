<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Application;

use ddziaduch\PenaltyPoints\Adapters\Secondary\InMemoryDriverFiles;
use ddziaduch\PenaltyPoints\Application\Ports\Primary\ImposePenalty;
use ddziaduch\PenaltyPoints\Domain\DriverFile;
use ddziaduch\PenaltyPoints\Domain\DrivingLicenseNoLongerValid;
use ddziaduch\PenaltyPoints\Domain\PenaltyImposed;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/** @covers \ddziaduch\PenaltyPoints\Application\ImposePenaltyService */
final class ImposePenaltyServiceIntegrationTest extends KernelTestCase
{
    public function testImposesPenaltyAndDispatchesEvents(): void
    {
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
        $service->impose($driverFile->licenseNumber, 10);
        $service->impose($driverFile->licenseNumber, 10);
        $service->impose($driverFile->licenseNumber, 10);

        self::assertCount(4, $eventSubscriber->events);
        self::assertFalse($driverFile->isDrivingLicenseValid($now));
    }

    private function getEventDispatcher(): EventDispatcherInterface
    {
        $eventDispatcher = self::getContainer()->get('event_dispatcher');
        assert($eventDispatcher instanceof EventDispatcherInterface);

        return $eventDispatcher;
    }

    private function getService(): ImposePenalty
    {
        $service = self::getContainer()->get(ImposePenalty::class);
        assert($service instanceof ImposePenalty);

        return $service;
    }

    private function getDriverFiles(): InMemoryDriverFiles
    {
        $driverFiles = self::getContainer()->get(InMemoryDriverFiles::class);
        assert($driverFiles instanceof InMemoryDriverFiles);

        return $driverFiles;
    }
}
