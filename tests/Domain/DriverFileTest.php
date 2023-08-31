<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Domain;

use ddziaduch\PenaltyPoints\Domain\DriverFile;
use ddziaduch\PenaltyPoints\Domain\Penalty;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ddziaduch\PenaltyPoints\Domain\DriverFile
 * @covers \ddziaduch\PenaltyPoints\Domain\Penalty
 */
final class DriverFileTest extends TestCase
{
    /** @dataProvider provideDataFromMaxNumberOfPenaltyPoints */
    public function testMaxNumberOfPenaltyPoints(
        \DateTimeImmutable $examPassedAt,
        \DateTimeImmutable $now,
        int $expectedNumberOfPenaltyPoints,
    ): void {
        $driver = new DriverFile($examPassedAt);
        self::assertSame(
            $expectedNumberOfPenaltyPoints,
            $driver->maxNumberOfPenaltyPoints($now),
        );
    }

    public static function provideDataFromMaxNumberOfPenaltyPoints(): iterable
    {
        $examPassedAt = new \DateTimeImmutable('2000-01-01T12:00:00Z');

        yield 'exam passed less than one year ago, 20 points' => [
            'examPassedAt' => $examPassedAt,
            'now' => $examPassedAt->modify('+10 months'),
            'expectedNumberOfPenaltyPoints' => 20,
        ];

        yield 'exam passed one year ago, 24 points' => [
            'examPassedAt' => $examPassedAt,
            'now' => $examPassedAt->modify('+1 year'),
            'expectedNumberOfPenaltyPoints' => 24,
        ];

        yield 'exam passed more than year ago, 24 points' => [
            'examPassedAt' => $examPassedAt,
            'now' => $examPassedAt->modify('+15 months'),
            'expectedNumberOfPenaltyPoints' => 24,
        ];
    }

    /**
     * @dataProvider isPenaltyPointsLimitExceededDataProvider
     *
     * @param Penalty[] $previousPenalties
     */
    public function testIsPenaltyPointsLimitExceeded(
        Penalty $newPenalty,
        \DateTimeImmutable $examPassedAt,
        \DateTimeImmutable $now,
        bool $expectedReturnValue,
        array $previousPenalties
    ): void {
        $driverFile = new DriverFile($examPassedAt, ...$previousPenalties);
        $driverFile->imposePenalty($newPenalty);
        self::assertSame(
            $expectedReturnValue,
            $driverFile->isPenaltyPointsLimitExceeded($now),
        );
    }

    public static function isPenaltyPointsLimitExceededDataProvider(): iterable
    {
        $examPassedAt = new \DateTimeImmutable('2020-05-29T04:30:00Z');

        yield 'exam passed less than year ago, got already 19 penalty points, just got new 2 penalty points' => [
            'newPenalty' => new Penalty($examPassedAt->modify('+11 months'), 2),
            'examPassedAt' => $examPassedAt,
            'now' => $examPassedAt->modify('+11 months'),
            'expectedReturnValue' => true,
            'previousPenalties' => [
                new Penalty($examPassedAt->modify('+1 month'), 10),
                new Penalty($examPassedAt->modify('+3 month'), 6),
                new Penalty($examPassedAt->modify('+6 month'), 3),
            ],
        ];

        yield 'exam passed less than year ago, got already 18 penalty points, just got new 2 penalty points' => [
            'newPenalty' => new Penalty($examPassedAt->modify('+11 months'), 2),
            'examPassedAt' => $examPassedAt,
            'now' => $examPassedAt->modify('+11 months'),
            'expectedReturnValue' => false,
            'previousPenalties' => [
                new Penalty($examPassedAt->modify('+1 month'), 10),
                new Penalty($examPassedAt->modify('+3 month'), 6),
                new Penalty($examPassedAt->modify('+6 month'), 2),
            ],
        ];

        // TODO: add more cases
    }
}
