<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Adapters\Secondary;

use DateTimeImmutable;
use Psr\Clock\ClockInterface;

final readonly class SystemClock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
