<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Adapters\Primary\Http;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\PoliceOfficer;
use Symfony\Component\HttpFoundation\Response;

final readonly class PoliceOfficerImposePenaltyHttpAdapter
{
    public function __construct(
        private PoliceOfficer $policeOfficer,
    ) {}

    public function __invoke(
        string $driverLicenseNumber,
        string $penaltySeries,
        int $penaltyNumber,
        int $numberOfPoints,
        bool $isPaidOnSpot,
    ): Response {
        try {
            $this->policeOfficer->imposePenalty(
                $driverLicenseNumber,
                $penaltySeries,
                $penaltyNumber,
                $numberOfPoints,
                $isPaidOnSpot,
            );
        } catch (\DomainException | \OutOfBoundsException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
