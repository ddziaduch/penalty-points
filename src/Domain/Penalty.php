<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Domain;

final readonly class Penalty
{
    public function __construct(
        public \DateTimeImmutable $createdAt,
        public int $numberOfPoints,
    ) {}

    public function isValid(\DateTimeImmutable $now): bool
    {
        return $this->createdAt->diff($now)->y < 2;
    }
}
