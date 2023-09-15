<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Adapters\Primary;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\PoliceOfficer;
use Symfony\Component\HttpFoundation\Response;

final readonly class PoliceOfficerHttpAdapter
{
    public function __construct(
        private PoliceOfficer $imposePenalty,
    ) {}

    public function __invoke(
        string $driverLicenseNumber,
        int $numberOfPoints,
    ): Response {
        $this->imposePenalty->imposePenalty($driverLicenseNumber, false, $numberOfPoints);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
