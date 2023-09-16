<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Adapters\Primary;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\PoliceOfficer;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/** @covers \ddziaduch\PenaltyPoints\Adapters\Primary\PoliceOfficerImposePenaltyHttpAdapter */
final class PoliceOfficerHttpAdapterTest extends WebTestCase
{
    private const DRIVER_LICENSE_NUMBER = '11111/22/3333';
    private const NUMBER_OF_POINTS = 10;

    public function testInvokesPortAndReturnsEmptyResponse(): void
    {
        $this->markTestIncomplete('fix me');

        $imposePenalty = $this->createMock(PoliceOfficer::class);
        $imposePenalty->expects(self::once())->method('imposeUnpaidPenalty')->with(
            self::DRIVER_LICENSE_NUMBER,
            self::NUMBER_OF_POINTS,
        );

        $client = self::createClient();
        self::getContainer()->set(PoliceOfficer::class, $imposePenalty);

        $client->request(
            method: 'POST',
            uri: sprintf(
                '/impose-penalty/driver/%s/points/%d',
                self::DRIVER_LICENSE_NUMBER,
                self::NUMBER_OF_POINTS,
            ),
        );
        $response = $client->getResponse();

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertEmpty($response->getContent());
    }
}
