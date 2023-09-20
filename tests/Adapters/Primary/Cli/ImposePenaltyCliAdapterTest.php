<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Adapters\Primary\Cli;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\ImposePenalty;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @covers \ddziaduch\PenaltyPoints\Adapters\Primary\Cli\ImposePenaltyCliAdapter
 *
 * @internal
 */
final class ImposePenaltyCliAdapterTest extends KernelTestCase
{
    public const DRIVER_LICENSE_NUMBER = 'lorem-ipsum';
    public const PENALTY_POINTS = 10;
    public const PENALTY_SERIES = 'CD';
    public const PENALTY_NUMBER = 12345;
    private const IS_PAID_ON_SPOT = true;

    public function testPassesArgumentsToThePort(): void
    {
        $kernel = self::bootKernel();

        $imposePenalty = $this->createMock(ImposePenalty::class);
        $imposePenalty->expects(self::once())->method('impose')->with(
            self::DRIVER_LICENSE_NUMBER,
            self::PENALTY_SERIES,
            self::PENALTY_NUMBER,
            self::PENALTY_POINTS,
            self::IS_PAID_ON_SPOT,
        );

        $command = $this->command($imposePenalty, $kernel);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'driverLicenseNumber' => self::DRIVER_LICENSE_NUMBER,
            'penaltySeries' => self::PENALTY_SERIES,
            'penaltyNumber' => (string) self::PENALTY_NUMBER,
            'numberOfPenaltyPoints' => (string) self::PENALTY_POINTS,
            'isPaidOnSpot' => (string) (int) self::IS_PAID_ON_SPOT,
        ]);
    }

    #[DataProvider('provideExceptions')]
    public function testOutputsExceptionsAsFailure(\Throwable $exception): void
    {
        $kernel = self::bootKernel();

        $imposePenalty = $this->createStub(ImposePenalty::class);
        $imposePenalty->method('impose')->willThrowException($exception);

        $command = $this->command($imposePenalty, $kernel);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'driverLicenseNumber' => self::DRIVER_LICENSE_NUMBER,
            'penaltySeries' => self::PENALTY_SERIES,
            'penaltyNumber' => (string) self::PENALTY_NUMBER,
            'numberOfPenaltyPoints' => (string) self::PENALTY_POINTS,
            'isPaidOnSpot' => (string) (int) self::IS_PAID_ON_SPOT,
        ]);

        self::assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }

    public static function provideExceptions(): \Generator
    {
        yield \DomainException::class => [new \DomainException('something terrible happened')];

        yield \OutOfBoundsException::class => [new \OutOfBoundsException('out of range sir!')];
    }

    private function command(ImposePenalty $policeOfficer, KernelInterface $kernel): Command
    {
        self::getContainer()->set(ImposePenalty::class, $policeOfficer);

        return (new Application($kernel))->find('police-officer:impose-penalty');
    }
}
