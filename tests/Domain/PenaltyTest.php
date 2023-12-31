<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Domain;

use ddziaduch\PenaltyPoints\Domain\Penalty;
use ddziaduch\PenaltyPoints\Domain\PenaltyAlreadyPaid;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Penalty
 *
 * @internal
 */
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
            'penalty' => Penalty::unpaid(
                series: 'CS',
                number: 12345,
                occurredAt: $now,
                numberOfPoints: 10
            ),
            'isValid' => true,
            'now' => $now,
        ];

        yield 'paid on spot, occurred less than 2 years ago' => [
            'penalty' => Penalty::paidOnSpot(
                series: 'CS',
                number: 12345,
                occurredAt: $now->modify('-1 years'),
                numberOfPoints: 10,
            ),
            'isValid' => true,
            'now' => $now,
        ];

        yield 'paid on spot, occurred 2 years ago' => [
            'penalty' => Penalty::paidOnSpot(
                series: 'CS',
                number: 12345,
                occurredAt: $now->modify('-2 years'),
                numberOfPoints: 10,
            ),
            'isValid' => false,
            'now' => $now,
        ];

        yield 'paid on spot, occurred more than 2 years ago' => [
            'penalty' => Penalty::paidOnSpot(
                series: 'CS',
                number: 12345,
                occurredAt: $now->modify('-3 years'),
                numberOfPoints: 10,
            ),
            'isValid' => false,
            'now' => $now,
        ];
    }

    public function testPayingAlreadyPaidPenalty(): void
    {
        $penalty = Penalty::paidOnSpot(
            series: 'CD',
            number: 12345,
            occurredAt: new \DateTimeImmutable(),
            numberOfPoints: 10,
        );

        $this->expectException(PenaltyAlreadyPaid::class);
        $penalty->pay(payedAt: new \DateTimeImmutable());
    }

    public function testPayingUnpaidPenalty(): void
    {
        $penalty = Penalty::unpaid(
            series: 'CD',
            number: 12345,
            occurredAt: new \DateTimeImmutable(),
            numberOfPoints: 10,
        );

        self::assertFalse($penalty->isPaid());
        $penalty->pay(payedAt: new \DateTimeImmutable());
        self::assertTrue($penalty->isPaid());
    }
}
