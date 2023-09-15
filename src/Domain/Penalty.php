<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Domain;

final class Penalty
{

    public function __construct(
        public readonly string $series,
        public readonly int $number,
        public readonly \DateTimeImmutable $occurredAt,
        public ?\DateTimeImmutable $payedAt,
        public readonly int $numberOfPoints,
    ) {}

    public function isValid(\DateTimeImmutable $now): bool
    {
        if ($this->payedAt === null) {
            return true;
        }

        return $this->payedAt->diff($now)->y < 2;
    }
}
