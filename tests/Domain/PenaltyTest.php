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
            'penalty' => Penalty::unpaid('CS', 12345, occurredAt: $now, numberOfPoints: 10),
            'isValid' => true,
            'now' => $now,
        ];

        yield 'paid, less than 2 years ago' => [
            'penalty' => Penalty::paid('CS', 12345, occurredAt: $now->modify('-1 years'), numberOfPoints: 10),
            'isValid' => true,
            'now' => $now,
        ];

        yield 'paid, 2 years ago' => [
            'penalty' => Penalty::paid('CS', 12345, occurredAt: $now->modify('-2 years'), numberOfPoints: 10),
            'isValid' => false,
            'now' => $now,
        ];

        yield 'paid, more than 2 years ago' => [
            'penalty' => Penalty::paid('CS',
                12345,
                occurredAt: $now->modify('-3 years'),
                numberOfPoints: 10),
            'isValid' => false,
            'now' => $now,
        ];
    }
}
