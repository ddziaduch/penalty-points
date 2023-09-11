<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Adapters\Primary;

use ddziaduch\PenaltyPoints\Adapters\Primary\ImposePenaltyHttpAdapter;
use ddziaduch\PenaltyPoints\Application\Ports\Primary\ImposePenalty;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/** @covers \ddziaduch\PenaltyPoints\Adapters\Primary\ImposePenaltyHttpAdapter */
final class ImposePenaltyHttpAdapterTest extends TestCase
{
    public function testInvokesPortAndReturnsEmptyResponse(): void
    {
        $driverLicenseNumber = '12345';
        $numberOfPoints = 10;

        $imposePenalty = $this->createMock(ImposePenalty::class);
        $imposePenalty->expects(self::once())->method('impose')->with(
            $driverLicenseNumber,
            $numberOfPoints,
        );

        $adapter = new ImposePenaltyHttpAdapter($imposePenalty);
        $response = ($adapter)($driverLicenseNumber, $numberOfPoints);

        self::assertEquals(
            new Response(status: Response::HTTP_NO_CONTENT),
            $response,
        );
    }
}
