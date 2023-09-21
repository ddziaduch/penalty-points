<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Domain;

final class PenaltyImposedButDriversLicenseIsNotValidAnymore extends \DomainException
{
    public function __construct(
        string $penaltySeries,
        int $penaltyNumber,
        string $driversLicenseNumber,
    ) {
        parent::__construct(
            sprintf(
                'Penalty series %s / number %s imposed, but the driver\'s license %s is not valid anymore',
                $penaltySeries,
                $penaltyNumber,
                $driversLicenseNumber,
            ),
        );
    }
}
