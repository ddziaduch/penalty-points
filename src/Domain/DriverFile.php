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
    public function imposePenaltyPaidOnSpot(
        string $series,
        int $number,
        \DateTimeImmutable $occurredAt,
        int $numberOfPoints
    ): void {
        $penalty = Penalty::paidOnSpot($series, $number, $occurredAt, $numberOfPoints);
        $this->imposePenaltyInternal($occurredAt, $penalty);
    }

    /** @throws \DomainException */
    public function imposeUnpaidPenalty(
        string $series,
        int $number,
        \DateTimeImmutable $occurredAt,
        int $numberOfPoints
    ): void {
        $penalty = Penalty::unpaid($series, $number, $occurredAt, $numberOfPoints);
        $this->imposePenaltyInternal($occurredAt, $penalty);
    }


    /**
     * @throws \OutOfBoundsException
     * @throws \DomainException
     */
    public function payPenalty(
        string $series,
        int $number,
        \DateTimeImmutable $payedAt,
    ): void {
        foreach ($this->penalties as $key => $penalty) {
            if ($penalty->series === $series && $penalty->number === $number) {
                if ($penalty->isPaid()) {
                    throw new \DomainException('Penalty already paid');
                }

                $this->penalties[$key] = $penalty->pay($payedAt);

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

    private function imposePenaltyInternal(\DateTimeImmutable $occurredAt, Penalty $penalty): void
    {
        if (!$this->isDrivingLicenseValid($occurredAt)) {
            throw new \DomainException('Can not impose penalty, drivers licence is not valid anymore');
        }

        $this->penalties[] = $penalty;
    }
}
