<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Adapters\Primary\Http;

use ddziaduch\PenaltyPoints\Application\DriverFileDoesNotExist;
use ddziaduch\PenaltyPoints\Application\Ports\Primary\ImposePenalty;
use ddziaduch\PenaltyPoints\Domain\PenaltyImposedButDriversLicenseIsNotValidAnymore;
use Symfony\Component\HttpFoundation\Response;

final readonly class ImposePenaltyHttpAdapter
{
    public function __construct(
        private ImposePenalty $imposePenalty,
    ) {}

    public function __invoke(
        string $driverLicenseNumber,
        string $penaltySeries,
        int $penaltyNumber,
        int $numberOfPoints,
        bool $isPaidOnSpot,
    ): Response {
        try {
            $this->imposePenalty->impose(
                $driverLicenseNumber,
                $penaltySeries,
                $penaltyNumber,
                $numberOfPoints,
                $isPaidOnSpot,
            );
        } catch (DriverFileDoesNotExist|PenaltyImposedButDriversLicenseIsNotValidAnymore $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
