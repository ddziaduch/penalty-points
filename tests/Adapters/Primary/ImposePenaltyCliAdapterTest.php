<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Adapters\Primary;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\ImposePenalty;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/** @covers \ddziaduch\PenaltyPoints\Adapters\Primary\ImposePenaltyCliAdapter */
final class ImposePenaltyCliAdapterTest extends KernelTestCase
{
    public function testExecution(): void
    {
        $driverLicenseNumber = '11111/22/3333';
        $penaltyPoints = '10';

        $kernel = self::bootKernel();

        $imposePenalty = $this->createMock(ImposePenalty::class);
        $imposePenalty->expects(self::once())->method('impose')->with($driverLicenseNumber, (int) $penaltyPoints);

        self::getContainer()->set(ImposePenalty::class, $imposePenalty);
        $application = new Application($kernel);

        $command = $application->find('app:impose-penalty');
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'driverLicenseNumber' => $driverLicenseNumber,
            'numberOfPoints' => $penaltyPoints,
        ]);
    }
}
