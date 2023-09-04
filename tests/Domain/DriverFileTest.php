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
    /** @dataProvider provideDataForMaxNumberOfPenaltyPoints */
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

    public static function provideDataForMaxNumberOfPenaltyPoints(): iterable
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
    public function testIsDrivingLicenseValid(
        \DateTimeImmutable $examPassedAt,
        array $previousPenalties,
        Penalty $newPenalty,
        bool $expectedReturnValue,
    ): void {
        $driverFile = new DriverFile($examPassedAt, ...$previousPenalties);
        $driverFile->imposePenalty($newPenalty);
        self::assertSame(
            $expectedReturnValue,
            $driverFile->isDrivingLicenseValid($newPenalty->createdAt),
        );
    }

    public static function isPenaltyPointsLimitExceededDataProvider(): iterable
    {
        $examPassedAt = new \DateTimeImmutable('2020-05-29T04:30:00Z');

        // exam passed less than year ago cases
        yield 'exam passed less than year ago, got already 19 penalty points, just got new 2 penalty points' => [
            'examPassedAt' => $examPassedAt,
            'previousPenalties' => [
                new Penalty($examPassedAt->modify('+1 month'), 10),
                new Penalty($examPassedAt->modify('+3 month'), 6),
                new Penalty($examPassedAt->modify('+6 month'), 3),
            ],
            'newPenalty' => new Penalty($examPassedAt->modify('+11 months'), 2),
            'expectedReturnValue' => false,
        ];

        yield 'exam passed less than year ago, got already 18 penalty points, just got new 2 penalty points' => [
            'examPassedAt' => $examPassedAt,
            'previousPenalties' => [
                new Penalty($examPassedAt->modify('+1 month'), 10),
                new Penalty($examPassedAt->modify('+3 month'), 6),
                new Penalty($examPassedAt->modify('+6 month'), 2),
            ],
            'newPenalty' => new Penalty($examPassedAt->modify('+11 months'), 2),
            'expectedReturnValue' => true,
        ];

        // exam passed more than year ago cases
        yield 'exam passed more than year ago, got already 19 penalty points, just got new 2 penalty points' => [
            'examPassedAt' => $examPassedAt,
            'previousPenalties' => [
                new Penalty($examPassedAt->modify('+1 month'), 10),
                new Penalty($examPassedAt->modify('+3 month'), 9),
            ],
            'newPenalty' => new Penalty($examPassedAt->modify('+15 months'), 2),
            'expectedReturnValue' => true,
        ];

        yield 'exam passed less than year ago, got already 18 penalty points, just got new 6 penalty points' => [
            'examPassedAt' => $examPassedAt,
            'previousPenalties' => [
                new Penalty($examPassedAt->modify('+1 month'), 10),
                new Penalty($examPassedAt->modify('+3 month'), 3),
                new Penalty($examPassedAt->modify('+4 month'), 3),
                new Penalty($examPassedAt->modify('+6 month'), 2),
            ],
            'newPenalty' => new Penalty($examPassedAt->modify('+18 months'), 6),
            'expectedReturnValue' => true,
        ];

        yield 'exam passed less than year ago, got already 18 penalty points, just got new 10 penalty points' => [
            'examPassedAt' => $examPassedAt,
            'previousPenalties' => [
                new Penalty($examPassedAt->modify('+1 month'), 10),
                new Penalty($examPassedAt->modify('+3 month'), 3),
                new Penalty($examPassedAt->modify('+4 month'), 3),
                new Penalty($examPassedAt->modify('+6 month'), 2),
            ],
            'newPenalty' => new Penalty($examPassedAt->modify('+18 months'), 10),
            'expectedReturnValue' => false,
        ];

        // expired penalties cases
        yield 'exam passed more than year ago, got already 18 penalty points, where 6 are expired, just got new 10 penalty points' => [
            'examPassedAt' => $examPassedAt,
            'previousPenalties' => [
                new Penalty($examPassedAt->modify('+1 month'), 6),
                new Penalty($examPassedAt->modify('+13 month'), 10),
                new Penalty($examPassedAt->modify('+26 month'), 2),
            ],
            'newPenalty' => new Penalty($examPassedAt->modify('+36 months'), 10),
            'expectedReturnValue' => true,
        ];

        yield 'exam passed more than year ago, got already 24 penalty points, where 2 are expired, just got new 10 penalty points' => [
            'examPassedAt' => $examPassedAt,
            'previousPenalties' => [
                new Penalty($examPassedAt->modify('+1 month'), 2),
                new Penalty($examPassedAt->modify('+13 month'), 10),
                new Penalty($examPassedAt->modify('+16 month'), 10),
                new Penalty($examPassedAt->modify('+26 month'), 2),
            ],
            'newPenalty' => new Penalty($examPassedAt->modify('+36 months'), 10),
            'expectedReturnValue' => false,
        ];
    }
}
