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

    /** @throws \DomainException */
    public function imposePenalty(
        string $series,
        int $number,
        \DateTimeImmutable $occurredAt,
        bool $isPaid,
        int $numberOfPoints
    ): void {
        if (!$this->isDrivingLicenseValid($occurredAt)) {
            throw new \DomainException('Can not impose penalty, drivers licence is not valid anymore!');
        }

        $this->penalties[] = new Penalty(
            series: $series,
            number: $number,
            occurredAt: $occurredAt,
            payedAt: $isPaid ? $occurredAt : null,
            numberOfPoints: $numberOfPoints,
        );
    }

    /** @throws \OutOfBoundsException */
    public function payPenalty(
        string $series,
        int $number,
        \DateTimeImmutable $now,
    ): void {
        foreach ($this->penalties as $penalty) {
            if ($penalty->series === $series && $penalty->number === $number) {
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
