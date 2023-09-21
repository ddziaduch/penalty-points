<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Domain;

use ddziaduch\PenaltyPoints\Domain\DriverFile;
use ddziaduch\PenaltyPoints\Domain\PenaltyDoesNotExist;
use ddziaduch\PenaltyPoints\Domain\PenaltyImposedButDriversLicenseIsNotValidAnymore;
use PHPUnit\Framework\TestCase;

/**
 * @covers \ddziaduch\PenaltyPoints\Domain\DriverFile
 *
 * @internal
 */
final class DriverFileTest extends TestCase
{
    private const LICENSE_NUMBER = '12345';
    private const PENALTY_SERIES = 'CS';

    /**
     * @dataProvider imposingPenaltiesWithoutReachingLimitsDataProvider
     *
     * @param array{ occurredAt: \DateTimeImmutable, isPaidOnSpot: bool, numberOfPoints: int }[] $penalties
     */
    public function testImposingPenaltiesWithoutReachingLimits(
        \DateTimeImmutable $examPassedAt,
        array $penalties,
    ): void {
        assert(!empty($penalties));

        $driverFile = new DriverFile(self::LICENSE_NUMBER, $examPassedAt);
        $sumOfPoints = 0;

        foreach ($penalties as $number => $penalty) {
            $sumOfPoints += $penalty['numberOfPoints'];
            $driverFile->imposePenalty(
                self::PENALTY_SERIES,
                $number,
                occurredAt: $penalty['occurredAt'],
                numberOfPoints: $penalty['numberOfPoints'],
                isPaidOnSpot: $penalty['isPaidOnSpot'],
            );
        }

        self::assertTrue(
            $driverFile->isDrivingLicenseValid($penalty['occurredAt']),
        );
        self::assertSame(
            $sumOfPoints,
            $driverFile->sumOfValidPenaltyPoints($penalty['occurredAt']),
        );
    }

    public static function imposingPenaltiesWithoutReachingLimitsDataProvider(): \Generator
    {
        $examPassedAt = new \DateTimeImmutable();

        yield 'fresh driver, got already 20 valid penalty points' => [
            'examPassedAt' => $examPassedAt,
            'penalties' => [
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
            ],
        ];

        yield 'senior driver, got already 24 valid penalty points' => [
            'examPassedAt' => $examPassedAt,
            'penalties' => [
                [
                    'occurredAt' => $examPassedAt->modify('+12 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 10,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+14 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 10,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+24 month'),
                    'isPaidOnSpot' => false,
                    'numberOfPoints' => 4,
                ],
            ],
        ];
    }

    /**
     * @dataProvider imposingPenaltiesReachingLimitsDataProvider
     *
     * @param array{ occurredAt: \DateTimeImmutable, isPaidOnSpot: bool, numberOfPoints: int }[] $penalties
     */
    public function testImposingPenaltiesReachingLimits(
        \DateTimeImmutable $examPassedAt,
        array $penalties,
    ): void {
        assert(!empty($penalties));

        $driverFile = new DriverFile(self::LICENSE_NUMBER, $examPassedAt);
        $sumOfPoints = array_reduce(
            $penalties,
            static fn (int $sum, array $penalty): int => $penalty['numberOfPoints'] + $sum,
            initial: 0
        );
        $lastPenalty = array_pop($penalties);

        foreach ($penalties as $number => $penalty) {
            $driverFile->imposePenalty(
                series: self::PENALTY_SERIES,
                number: $number,
                occurredAt: $penalty['occurredAt'],
                numberOfPoints: $penalty['numberOfPoints'],
                isPaidOnSpot: $penalty['isPaidOnSpot'],
            );
        }

        $this->expectException(PenaltyImposedButDriversLicenseIsNotValidAnymore::class);

        try {
            $driverFile->imposePenalty(
                series: self::PENALTY_SERIES,
                number: 999,
                occurredAt: $lastPenalty['occurredAt'],
                numberOfPoints: $lastPenalty['numberOfPoints'],
                isPaidOnSpot: $lastPenalty['isPaidOnSpot'],
            );
        } finally {
            self::assertFalse(
                $driverFile->isDrivingLicenseValid($lastPenalty['occurredAt']),
            );
            self::assertSame(
                $sumOfPoints,
                $driverFile->sumOfValidPenaltyPoints($lastPenalty['occurredAt']),
            );
        }
    }

    public static function imposingPenaltiesReachingLimitsDataProvider(): \Generator
    {
        $examPassedAt = new \DateTimeImmutable();

        yield 'fresh driver, got already 18 valid penalty points, then 3 penalty points' => [
            'examPassedAt' => $examPassedAt,
            'penalties' => [
                [
                    'occurredAt' => $examPassedAt->modify('+1 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 10,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+3 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 8,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+6 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 3,
                ],
            ],
        ];

        yield 'senior driver, got already 20 valid penalty points, then 5 penalty points' => [
            'examPassedAt' => $examPassedAt,
            'penalties' => [
                [
                    'occurredAt' => $examPassedAt->modify('+6 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 10,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+12 month'),
                    'isPaidOnSpot' => true,
                    'numberOfPoints' => 6,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+18 month'),
                    'isPaidOnSpot' => false,
                    'numberOfPoints' => 4,
                ],
                [
                    'occurredAt' => $examPassedAt->modify('+24 month'),
                    'isPaidOnSpot' => false,
                    'numberOfPoints' => 5,
                ],
            ],
        ];
    }

    public function testPayingPenalty(): void
    {
        $this->expectNotToPerformAssertions(); // we do not expect exception here
        $now = new \DateTimeImmutable();
        $driverFile = new DriverFile(self::LICENSE_NUMBER, $now->modify('-36 months'));
        $driverFile->imposePenalty(
            series: self::PENALTY_SERIES,
            number: 1,
            occurredAt: $now->modify('-1 months'),
            numberOfPoints: 12,
            isPaidOnSpot: false,
        );
        $driverFile->payPenalty(series: self::PENALTY_SERIES, number: 1, payedAt: $now);
    }

    public function testPayingUnknownPenalty(): void
    {
        $now = new \DateTimeImmutable();
        $driverFile = new DriverFile(self::LICENSE_NUMBER, $now->modify('-36 months'));

        $this->expectException(PenaltyDoesNotExist::class);
        $driverFile->payPenalty(series: self::PENALTY_SERIES, number: 12345, payedAt: $now->modify('-5 months'));
    }
}
