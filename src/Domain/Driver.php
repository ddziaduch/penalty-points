<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Domain;

final class Driver
{
    /** @var Penalty[] */
    private array $penalties;

    public function __construct(
        private \DateTimeImmutable $examPassedAt,
        Penalty ...$penalties,
    ) {
        $this->penalties = $penalties;
    }

    public function imposePenalty(Penalty $penalty): void
    {
        $this->penalties[] = $penalty;
    }

    public function isPenaltyPointsLimitExceeded(\DateTimeImmutable $now): bool
    {

    }

    public function maxNumberOfPenaltyPoints(\DateTimeImmutable $now): int
    {
        if ($now->diff($this->examPassedAt)->y >= 1) {
            return 24;
        }

        return 20;
    }
}
