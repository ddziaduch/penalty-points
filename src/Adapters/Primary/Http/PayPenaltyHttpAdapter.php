<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Adapters\Primary\Http;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\PayPenalty;
use Symfony\Component\HttpFoundation\Response;

final readonly class PayPenaltyHttpAdapter
{
    public function __construct(
        private PayPenalty $payPenalty,
    ) {}

    public function __invoke(
        string $driverLicenseNumber,
        string $penaltySeries,
        int $penaltyNumber,
    ): Response {
        try {
            $this->payPenalty->pay(
                $driverLicenseNumber,
                $penaltySeries,
                $penaltyNumber,
            );
        } catch (\DomainException|\OutOfBoundsException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
