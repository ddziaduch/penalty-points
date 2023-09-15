<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Domain;

use ddziaduch\PenaltyPoints\Domain\Penalty;
use PHPUnit\Framework\TestCase;

/** @covers Penalty */
class PenaltyTest extends TestCase
{
    /** @dataProvider provideData */
    public function testIsValid(Penalty $penalty, bool $isValid, \DateTimeImmutable $now): void
    {
        self::assertSame($isValid, $penalty->isValid($now));
    }

    public static function provideData(): \Generator
    {
        $now = new \DateTimeImmutable();

        yield 'unpaid' => [
            'penalty' => new Penalty('CS', 12345, occurredAt: $now, payedAt: null, numberOfPoints: 10),
            'isValid' => true,
            'now' => $now,
        ];

        $oneYearAgo = $now->modify('-1 years');
        yield 'paid, less than 2 years ago' => [
            'penalty' => new Penalty('CS', 12345, occurredAt: $oneYearAgo, payedAt: $oneYearAgo, numberOfPoints: 10),
            'isValid' => true,
            'now' => $now,
        ];

        $twoYearsAgo = $now->modify('-2 years');
        yield 'paid, 2 years ago' => [
            'penalty' => new Penalty('CS', 12345, occurredAt: $twoYearsAgo, payedAt: $twoYearsAgo, numberOfPoints: 10),
            'isValid' => false,
            'now' => $now,
        ];

        $threeYearsAgo = $now->modify('-3 years');
        yield 'paid, more than 2 years ago' => [
            'penalty' => new Penalty('CS',
                12345,
                occurredAt: $threeYearsAgo,
                payedAt: $threeYearsAgo,
                numberOfPoints: 10),
            'isValid' => false,
            'now' => $now,
        ];
    }
}
