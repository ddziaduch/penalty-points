<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Domain;

final class DriverFile
{
    /** @var Penalty[] */
    private array $penalties;
    /** @var object[] */
    private array $events;

    public function __construct(
        public readonly string $licenseNumber,
        public readonly \DateTimeImmutable $examPassedAt,
        Penalty ...$penalties,
    ) {
        $this->penalties = $penalties;
    }

    public function imposePenalty(Penalty $penalty): void
    {
        $this->penalties[] = $penalty;
        $this->events[] = new PenaltyImposed($this->licenseNumber, $penalty);

        if (!$this->isDrivingLicenseValid($penalty->createdAt)) {
            $this->events[] = new DrivingLicenseNoLongerValid($this->licenseNumber);
        }
    }

    public function isDrivingLicenseValid(\DateTimeImmutable $now): bool
    {
        return $this->sumOfValidPenaltyPoints($now) <= $this->maxNumberOfPenaltyPoints($now);
    }

    public function sumOfValidPenaltyPoints(\DateTimeImmutable $now): int
    {
        $sum = 0;

        foreach ($this->penalties as $penalty) {
            if ($penalty->isValid($now)) {
                $sum += $penalty->numberOfPoints;
            }
        }

        return $sum;
    }

    public function maxNumberOfPenaltyPoints(\DateTimeImmutable $now): int
    {
        if ($this->examPassedAt->diff($now)->y >= 1) {
            return 24;
        }

        return 20;
    }

    /** @return object[] */
    public function dumpEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
