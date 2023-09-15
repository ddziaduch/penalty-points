<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Domain;

use ddziaduch\PenaltyPoints\Domain\DriverFile;
use PHPUnit\Framework\TestCase;

/** @covers \ddziaduch\PenaltyPoints\Domain\DriverFile */
final class DriverFileTest extends TestCase
{
    /** @dataProvider provideDataForMaxNumberOfPenaltyPoints */
    public function testMaxNumberOfPenaltyPoints(
        \DateTimeImmutable $examPassedAt,
        \DateTimeImmutable $now,
        int $expectedNumberOfPenaltyPoints,
    ): void {
        $driver = new DriverFile('12345', $examPassedAt);
        self::assertSame(
            $expectedNumberOfPenaltyPoints,
            $driver->maxNumberOfPenaltyPoints($now),
        );
    }

    public static function provideDataForMaxNumberOfPenaltyPoints(): \Generator
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
     * @dataProvider isDrivingLicenseValidDataProvider
     *
     * @param array{ occurredAt: \DateTimeImmutable, isPaid: bool, numberOfPoints: int } $previousPenalties
     */
    public function testIsDrivingLicenseValid(
        \DateTimeImmutable $examPassedAt,
        array $previousPenalties,
        \DateTimeImmutable $now,
        bool $isDrivingLicenseValid,
    ): void
    {
        $driverFile = new DriverFile('12345', $examPassedAt);
        foreach ($previousPenalties as $previousPenalty) {
            $driverFile->imposePenalty(
                now: $previousPenalty['occurredAt'],
                isPaid: $previousPenalty['isPaid'],
                numberOfPoints: $previousPenalty['numberOfPoints'],
            );
        }
        self::assertSame(
            $isDrivingLicenseValid,
            $driverFile->isDrivingLicenseValid($now),
        );
    }

    public static function isDrivingLicenseValidDataProvider(): \Generator
    {
        $examPassedAt = new \DateTimeImmutable('2020-05-29T04:30:00Z');

        // exam passed less than year ago cases
        yield 'exam passed less than year ago, got already 19 valid penalty points' => [
            'examPassedAt' => $examPassedAt,
            'previousPenalties' => [
                [
                    'occurredAt' => $examPassedAt->modify('+1 month'),
                    'isPaid' => true,
                    'numberOfPoints' => 10,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+3 month'),
                    'isPaid' => true,
                    'numberOfPoints' => 6,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+6 month'),
                    'isPaid' => true,
                    'numberOfPoints' => 3,
                ],
            ],
            'now' => $examPassedAt->modify('+11 months'),
            'isDrivingLicenseValid' => false,
        ];

        yield 'exam passed less than year ago, got already 18 valid penalty points' => [
            'examPassedAt' => $examPassedAt,
            'previousPenalties' => [
                [
                    'occurredAt' => $examPassedAt->modify('+1 month'),
                    'isPaid' => true,
                    'numberOfPoints' => 10,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+3 month'),
                    'isPaid' => true,
                    'numberOfPoints' => 6,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+6 month'),
                    'isPaid' => false,
                    'numberOfPoints' => 2,
                ],
            ],
            'now' => $examPassedAt->modify('+11 months'),
            'isDrivingLicenseValid' => true,
        ];

        // exam passed more than year ago cases
        yield 'exam passed more than year ago, got already 19 valid penalty points' => [
            'examPassedAt' => $examPassedAt,
            'previousPenalties' => [
                [
                    'occurredAt' => $examPassedAt->modify('+1 month'),
                    'isPaid' => true,
                    'numberOfPoints' => 10,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+3 month'),
                    'isPaid' => false,
                    'numberOfPoints' => 9,
                ],
            ],
            'now' => $examPassedAt->modify('+15 months'),
            'isDrivingLicenseValid' => true,
        ];

        yield 'exam passed less than year ago, got already 18 valid penalty points' => [
            'examPassedAt' => $examPassedAt,
            'previousPenalties' => [
                [
                    'occurredAt' => $examPassedAt->modify('+1 month'),
                    'isPaid' => true,
                    'numberOfPoints' => 10,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+3 month'),
                    'isPaid' => true,
                    'numberOfPoints' => 3,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+4 month'),
                    'isPaid' => true,
                    'numberOfPoints' => 3,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+6 month'),
                    'isPaid' => true,
                    'numberOfPoints' => 2,
                ],
            ],
            'now' => $examPassedAt->modify('+18 months'),
            'isDrivingLicenseValid' => false,
        ];

        // expired penalties cases
        yield 'exam passed more than year ago, got already 18 valid penalty points, where 6 are expired' => [
            'examPassedAt' => $examPassedAt,
            'previousPenalties' => [
                [
                    'occurredAt' => $examPassedAt->modify('+1 month'),
                    'isPaid' => true,
                    'numberOfPoints' => 6,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+13 month'),
                    'isPaid' => true,
                    'numberOfPoints' => 10,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+26 month'),
                    'isPaid' => true,
                    'numberOfPoints' => 2,
                ],
            ],
            'now' => $examPassedAt->modify('+36 months'),
            'isDrivingLicenseValid' => true,
        ];
    }
}
