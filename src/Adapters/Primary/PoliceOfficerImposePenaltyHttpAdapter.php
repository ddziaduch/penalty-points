<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Adapters\Primary;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\PoliceOfficer;
use Symfony\Component\HttpFoundation\Response;

final readonly class PoliceOfficerImposePenaltyHttpAdapter
{
    public function __construct(
        private PoliceOfficer $policeOfficer,
    ) {}

    public function __invoke(
        string $driverLicenseNumber,
        int $numberOfPoints,
    ): Response {
        $this->policeOfficer->imposePenalty($driverLicenseNumber, 'CS', 12345, $numberOfPoints, false);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
