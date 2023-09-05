<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Domain;

final readonly class PenaltyImposed
{
    public function __construct(
        public string $licenseNumber,
        public Penalty $penalty
    ) {}
}
