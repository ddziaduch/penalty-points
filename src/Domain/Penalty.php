<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Domain;

final readonly class Penalty
{
    private function __construct(
        public string $series,
        public int $number,
        public \DateTimeImmutable $occurredAt,
        public ?\DateTimeImmutable $payedAt,
        public int $numberOfPoints,
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

    public function pay(\DateTimeImmutable $payedAt): self
    {
        return new self(
            $this->series,
            $this->number,
            $this->occurredAt,
            $payedAt,
            $this->numberOfPoints,
        );
    }

    public function isPaid(): bool
    {
        return null !== $this->payedAt;
    }

    public function isValid(\DateTimeImmutable $now): bool
    {
        if (null === $this->payedAt) {
            return true;
        }

        return $this->payedAt->diff($now)->y < 2;
    }
}
