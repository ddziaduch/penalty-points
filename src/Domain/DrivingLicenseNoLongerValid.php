<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Domain;

final readonly class DrivingLicenseNoLongerValid
{
    public function __construct(public string $licenseNumber) {}
}
