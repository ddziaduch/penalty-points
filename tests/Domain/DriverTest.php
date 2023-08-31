<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Domain;

use ddziaduch\PenaltyPoints\Domain\Driver;
use PHPUnit\Framework\TestCase;

/** @covers Driver */
final class DriverTest extends TestCase
{
    /** @dataProvider provideDataFromMaxNumberOfPenaltyPoints */
    public function testMaxNumberOfPenaltyPoints(
        \DateTimeImmutable $examPassedAt,
        \DateTimeImmutable $currentDateTime,
        int $expectedNumberOfPenaltyPoints,
    ): void {
        $driver = new Driver($examPassedAt);
        self::assertSame(
            $expectedNumberOfPenaltyPoints,
            $driver->maxNumberOfPenaltyPoints($currentDateTime),
        );
    }

    public static function provideDataFromMaxNumberOfPenaltyPoints(): iterable
    {
        $examPassedAt = new \DateTimeImmutable('2000-01-01T12:00:00Z');

        yield 'exam passed less than one year ago, 20 points' => [
            'examPassedAt' => $examPassedAt,
            'currentDateTime' => $examPassedAt->modify('+10 months'),
            'expectedNumberOfPenaltyPoints' => 20,
        ];

        yield 'exam passed one year ago, 24 points' => [
            'examPassedAt' => $examPassedAt,
            'currentDateTime' => $examPassedAt->modify('+1 year'),
            'expectedNumberOfPenaltyPoints' => 24,
        ];

        yield 'exam passed more than year ago, 24 points' => [
            'examPassedAt' => $examPassedAt,
            'currentDateTime' => $examPassedAt->modify('+15 months'),
            'expectedNumberOfPenaltyPoints' => 24,
        ];
    }
}
