<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Adapters\Primary;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\ImposePenalty;
use Symfony\Component\HttpFoundation\Response;

final readonly class ImposePenaltyHttpAdapter
{
    public function __construct(
        private ImposePenalty $imposePenalty,
    ) {}

    public function __invoke(
        string $driverLicenseNumber,
        int $numberOfPoints,
    ): Response {
        $this->imposePenalty->impose($driverLicenseNumber, $numberOfPoints);

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
