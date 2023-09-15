<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Adapters\Primary;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\PoliceOfficer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/** @covers \ddziaduch\PenaltyPoints\Adapters\Primary\PoliceOfficerCliAdapter */
final class PoliceOfficerCliAdapterTest extends KernelTestCase
{
    public function testExecution(): void
    {
        self::markTestIncomplete('fix me');

        $driverLicenseNumber = '11111/22/3333';
        $penaltyPoints = '10';

        $kernel = self::bootKernel();

        $imposePenalty = $this->createMock(PoliceOfficer::class);
        $imposePenalty->expects(self::once())->method('imposePenalty')->with($driverLicenseNumber, (int) $penaltyPoints);

        self::getContainer()->set(PoliceOfficer::class, $imposePenalty);
        $application = new Application($kernel);

        $command = $application->find('app:impose-penalty');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'driverLicenseNumber' => $driverLicenseNumber,
            'numberOfPoints' => $penaltyPoints,
        ]);
    }
}
