<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Adapters\Secondary;

use Psr\Clock\ClockInterface;

final readonly class FixedClock implements ClockInterface
{
    public function __construct(private \DateTimeImmutable $now) {}

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }
}
