<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Domain;

use ddziaduch\PenaltyPoints\Domain\DriverFile;
use PHPUnit\Framework\TestCase;

/** @covers \ddziaduch\PenaltyPoints\Domain\DriverFile */
final class DriverFileTest extends TestCase
{
    private const LICENSE_NUMBER = '12345';
    private const PENALTY_SERIES = 'CS';

    /** @dataProvider provideDataForMaxNumberOfPenaltyPoints */
    public function testMaxNumberOfPenaltyPoints(
        \DateTimeImmutable $examPassedAt,
        \DateTimeImmutable $now,
        int $expectedNumberOfPenaltyPoints,
    ): void {
        $driver = new DriverFile(self::LICENSE_NUMBER, $examPassedAt);
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
     * @param array{ occurredAt: \DateTimeImmutable, isPaidOnSpot: bool, numberOfPoints: int } $previousPenalties
     */
    public function testIsDrivingLicenseValid(
        \DateTimeImmutable $examPassedAt,
        array $previousPenalties,
        \DateTimeImmutable $now,
        bool $isDrivingLicenseValid,
    ): void {
        $driverFile = new DriverFile(self::LICENSE_NUMBER, $examPassedAt);

        foreach ($previousPenalties as $number => $previousPenalty) {
            $driverFile->imposePenalty(
                self::PENALTY_SERIES,
                $number,
                occurredAt: $previousPenalty['occurredAt'],
                numberOfPoints: $previousPenalty['numberOfPoints'],
                isPaidOnSpot: $previousPenalty['isPaidOnSpot'],
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
        yield 'exam passed less than year ago, got already 21 valid penalty points' => [
            'examPassedAt' => $examPassedAt,
            'previousPenalties' => [
                [
                    'occurredAt' => $examPassedAt->modify('+1 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 10,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+3 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 6,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+6 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 3,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+3 month'),
                    'isPaidOnSpot' => true,
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
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 10,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+3 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 6,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+6 month'),
                    'isPaidOnSpot' => false,
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
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 10,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+3 month'),
                    'isPaidOnSpot' => false,
                    'numberOfPoints' => 9,
                ],
            ],
            'now' => $examPassedAt->modify('+15 months'),
            'isDrivingLicenseValid' => true,
        ];

        yield 'exam passed more than year ago, got already 18 valid penalty points' => [
            'examPassedAt' => $examPassedAt,
            'previousPenalties' => [
                [
                    'occurredAt' => $examPassedAt->modify('+1 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 10,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+3 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 3,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+4 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 3,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+6 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 2,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+3 month'),
                    'isPaidOnSpot' => false,
                    'numberOfPoints' => 8,
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
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 6,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+13 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 10,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+26 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 2,
                ],
            ],
            'now' => $examPassedAt->modify('+36 months'),
            'isDrivingLicenseValid' => true,
        ];
    }

    public function testImposePenaltyWhenReachedPenaltyPointsLimit(): void
    {
        $now = new \DateTimeImmutable();

        $driverFile = new DriverFile(self::LICENSE_NUMBER, $now->modify('-8 months'));
        $driverFile->imposePenalty(
            series: self::PENALTY_SERIES,
            number: 1,
            occurredAt: $now->modify('-6 months'),
            numberOfPoints: 12,
            isPaidOnSpot: true,
        );
        $driverFile->imposePenalty(
            series: self::PENALTY_SERIES,
            number: 2,
            occurredAt: $now->modify('-3 months'),
            numberOfPoints: 12,
            isPaidOnSpot: true,
        );

        $this->expectException(\DomainException::class);
        $driverFile->imposePenalty(
            series: self::PENALTY_SERIES,
            number: 3,
            occurredAt: $now,
            numberOfPoints: 12,
            isPaidOnSpot: false,
        );
    }

    public function testPayingPenalty(): void
    {
        $this->expectNotToPerformAssertions();
        $now = new \DateTimeImmutable();
        $driverFile = new DriverFile(self::LICENSE_NUMBER, $now->modify('-36 months'));
        $penaltyOccurredAt = $now->modify('-6 months');
        $driverFile->imposePenalty(
            series: self::PENALTY_SERIES,
            number: 1,
            occurredAt: $penaltyOccurredAt,
            numberOfPoints: 12,
            isPaidOnSpot: false,
        );
        $driverFile->payPenalty(series: self::PENALTY_SERIES, number: 1, payedAt: $now->modify('-5 months'));
    }

    public function testPayingUnknownPenalty(): void
    {
        $now = new \DateTimeImmutable();
        $driverFile = new DriverFile(self::LICENSE_NUMBER, $now->modify('-36 months'));
        $this->expectException(\OutOfBoundsException::class);
        $driverFile->payPenalty(series: self::PENALTY_SERIES, number: 12345, payedAt: $now->modify('-5 months'));
    }

    public function testPayingAlreadyPaidPenalty(): void
    {
        $now = new \DateTimeImmutable();
        $driverFile = new DriverFile(self::LICENSE_NUMBER, $now->modify('-36 months'));
        $this->expectException(\OutOfBoundsException::class);
        $penaltyOccurredAt = $now->modify('-6 months');
        $driverFile->imposePenalty(
            series: self::PENALTY_SERIES,
            number: 1,
            occurredAt: $penaltyOccurredAt,
            numberOfPoints: 12,
            isPaidOnSpot: false,
        );
        $driverFile->payPenalty(series: self::PENALTY_SERIES, number: 1, payedAt: $now->modify('-5 months'));

        $this->expectException(\DomainException::class);
        $driverFile->payPenalty(series: self::PENALTY_SERIES, number: 1, payedAt: $now->modify('-5 months'));
    }
}
