<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Domain;

use ddziaduch\PenaltyPoints\Domain\DriverFile;
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
        $this->expectNotToPerformAssertions(); // we do not expect exception here

        $driverFile = new DriverFile(self::LICENSE_NUMBER, $examPassedAt);

        foreach ($penalties as $number => $previousPenalty) {
            $driverFile->imposePenalty(
                self::PENALTY_SERIES,
                $number,
                occurredAt: $previousPenalty['occurredAt'],
                numberOfPoints: $previousPenalty['numberOfPoints'],
                isPaidOnSpot: $previousPenalty['isPaidOnSpot'],
            );
        }
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
        $driverFile = new DriverFile(self::LICENSE_NUMBER, $examPassedAt);

        $this->expectException(\DomainException::class);
        foreach ($penalties as $number => $previousPenalty) {
            $driverFile->imposePenalty(
                self::PENALTY_SERIES,
                $number,
                occurredAt: $previousPenalty['occurredAt'],
                numberOfPoints: $previousPenalty['numberOfPoints'],
                isPaidOnSpot: $previousPenalty['isPaidOnSpot'],
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
