<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Adapters\Primary;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\PoliceOfficer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

/** @covers \ddziaduch\PenaltyPoints\Adapters\Primary\Http\PoliceOfficerImposePenaltyCliAdapter */
final class PoliceOfficerCliAdapterTest extends KernelTestCase
{
    private const DRIVER_LICENSE_NUMBER = '11111/22/3333';
    private const PENALTY_POINTS = '10';
    private const PENALTY_SERIES = 'CD';
    private const PENALTY_NUMBER = '12345';
    private const IS_PAID_ON_SPOT = '1';

    public function testPassesArgumentsToThePort(): void
    {
        $kernel = self::bootKernel();

        $policeOfficer = $this->createMock(PoliceOfficer::class);
        $policeOfficer->expects(self::once())->method('imposePenalty')->with(
            self::DRIVER_LICENSE_NUMBER,
            self::PENALTY_SERIES,
            (int) self::PENALTY_NUMBER,
            (int) self::PENALTY_POINTS,
            (bool) self::IS_PAID_ON_SPOT,
        );

        $command = $this->command($policeOfficer, $kernel);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'driverLicenseNumber' => self::DRIVER_LICENSE_NUMBER,
            'penaltySeries' => self::PENALTY_SERIES,
            'penaltyNumber' => self::PENALTY_NUMBER,
            'numberOfPenaltyPoints' => self::PENALTY_POINTS,
            'isPaidOnSpot' => self::IS_PAID_ON_SPOT,
        ]);
    }

    /**
     * @dataProvider provideExceptions
     */
    public function testOutputsExceptionsAsFailure(\Throwable $exception): void
    {
        $kernel = self::bootKernel();

        $policeOfficer = $this->createStub(PoliceOfficer::class);
        $policeOfficer->method('imposePenalty')->willThrowException($exception);

        $command = $this->command($policeOfficer, $kernel);
        $commandTester = new CommandTester($command);

        $result = $commandTester->execute([
            'driverLicenseNumber' => self::DRIVER_LICENSE_NUMBER,
            'penaltySeries' => self::PENALTY_SERIES,
            'penaltyNumber' => self::PENALTY_NUMBER,
            'numberOfPenaltyPoints' => self::PENALTY_POINTS,
            'isPaidOnSpot' => self::IS_PAID_ON_SPOT,
        ]);

        self::assertSame(Command::FAILURE, $result);
    }

    public static function provideExceptions(): \Generator
    {
        yield \DomainException::class => [
            'exception' => new \DomainException('something terrible happened'),
        ];

        yield \OutOfBoundsException::class => [
            'exception' => new \OutOfBoundsException('out of range sir!'),
        ];
    }

    private function command(PoliceOfficer $policeOfficer, KernelInterface $kernel): Command
    {
        self::getContainer()->set(PoliceOfficer::class, $policeOfficer);

        return (new Application($kernel))->find('police-officer:impose-penalty');
    }
}
