<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Domain;

final class DriverFile
{
    /** @var Penalty[] */
    private array $penalties = [];

    public function __construct(
        public readonly string $licenseNumber,
        public readonly \DateTimeImmutable $examPassedAt,
    ) {
    }

    public function imposePenalty(\DateTimeImmutable $now, bool $isPaid, int $numberOfPoints): void
    {
        if (!$this->isDrivingLicenseValid($now)) {
            throw new \DomainException('Can not impose penalty, drivers licence is not valid anymore!');
        }

        $this->penalties[] = new Penalty(
            occurredAt: $now,
            payedAt: $isPaid ? $now : null,
            numberOfPoints: $numberOfPoints,
        );
    }

    public function payPenalty(\DateTimeImmutable $penaltyOccurredAt, \DateTimeImmutable $now): void
    {
        foreach ($this->penalties as $penalty) {
            if ($penalty->occurredAt === $penaltyOccurredAt) {
                $penalty->payedAt = $now;

                return;
            }
        }

        throw new \OutOfBoundsException('Penalty now found');
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
}
