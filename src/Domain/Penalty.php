<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Domain;

final class Penalty
{
    private function __construct(
        public readonly string $series,
        public readonly int $number,
        public readonly \DateTimeImmutable $occurredAt,
        public ?\DateTimeImmutable $payedAt,
        public readonly int $numberOfPoints,
    ) {}

    public static function unpaid(
        string $series,
        int $number,
        \DateTimeImmutable $occurredAt,
        int $numberOfPoints,
    ): self {
        return new self($series, $number, $occurredAt, null, $numberOfPoints);
    }

    public static function paidOnSpot(
        string $series,
        int $number,
        \DateTimeImmutable $occurredAt,
        int $numberOfPoints,
    ): self {
        return new self($series, $number, $occurredAt, $occurredAt, $numberOfPoints);
    }

    /** @throws \DomainException */
    public function pay(\DateTimeImmutable $payedAt): void
    {
        if ($this->isPaid()) {
            throw new \DomainException('Penalty already paid');
        }

        $this->payedAt = $payedAt;
    }

    public function isValid(\DateTimeImmutable $now): bool
    {
        if (null === $this->payedAt) {
            return true;
        }

        return $this->payedAt->diff($now)->y < 2;
    }

    public function isPaid(): bool
    {
        return null !== $this->payedAt;
    }
}
