<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Domain;

final class DriverFile
{
    /** @var Penalty[] */
    private array $penalties;

    public function __construct(
        private readonly \DateTimeImmutable $examPassedAt,
        Penalty ...$penalties,
    ) {
        $this->penalties = $penalties;
    }

    public function imposePenalty(Penalty $penalty): void
    {
        $this->penalties[] = $penalty;
    }

    public function isDrivingLicenseValid(\DateTimeImmutable $now): bool
    {
        return $this->sumOfValidPenaltyPoints($now) <= $this->maxNumberOfPenaltyPoints($now);
    }

    public function sumOfValidPenaltyPoints(\DateTimeImmutable $now): int
    {
        $got = 0;

        foreach ($this->penalties as $penalty) {
            if ($penalty->isValid($now)) {
                $got += $penalty->numberOfPoints;
            }
        }
        return $got;
    }

    public function maxNumberOfPenaltyPoints(\DateTimeImmutable $now): int
    {
        if ($this->examPassedAt->diff($now)->y >= 1) {
            return 24;
        }

        return 20;
    }
}
