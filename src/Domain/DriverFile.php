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
    ) {}

    /** @throws PenaltyImposedButDriversLicenseIsNotValidAnymore */
    public function imposePenalty(
        string $series,
        int $number,
        \DateTimeImmutable $occurredAt,
        int $numberOfPoints,
        bool $isPaidOnSpot,
    ): void {
        $penalty = $isPaidOnSpot
            ? Penalty::paidOnSpot($series, $number, $occurredAt, $numberOfPoints)
            : Penalty::unpaid($series, $number, $occurredAt, $numberOfPoints);

        $this->penalties[] = $penalty;

        if (!$this->isDrivingLicenseValid($occurredAt)) {
            throw new PenaltyImposedButDriversLicenseIsNotValidAnymore(
                $penalty->series,
                $penalty->number,
                $this->licenseNumber,
            );
        }
    }

    /**
     * @throws PenaltyDoesNotExist
     * @throws PenaltyAlreadyPaid
     */
    public function payPenalty(
        string $series,
        int $number,
        \DateTimeImmutable $payedAt,
    ): void {
        foreach ($this->penalties as $penalty) {
            if ($penalty->series === $series && $penalty->number === $number) {
                $penalty->pay($payedAt);

                return;
            }
        }

        throw new PenaltyDoesNotExist($series, $number);
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

    private function maxNumberOfPenaltyPoints(\DateTimeImmutable $now): int
    {
        if ($this->examPassedAt->diff($now)->y >= 1) {
            return 24;
        }

        return 20;
    }
}
