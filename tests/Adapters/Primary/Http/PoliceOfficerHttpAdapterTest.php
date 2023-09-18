<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Adapters\Primary\Http;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\ImposePenalty;
use ddziaduch\PenaltyPoints\Tests\Adapters\Primary\Cli\PoliceOfficerCliAdapterTest;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \ddziaduch\PenaltyPoints\Adapters\Primary\Http\PoliceOfficerImposePenaltyHttpAdapter
 *
 * @internal
 */
final class PoliceOfficerHttpAdapterTest extends WebTestCase
{
    #[DataProvider('provideIsPaidOnSpot')]
    public function testPassesArgumentsToThePort(bool $isPaidOnSpot): void
    {
        $policeOfficer = $this->createMock(ImposePenalty::class);
        $policeOfficer->expects(self::once())->method('impose')->with(
            PoliceOfficerCliAdapterTest::DRIVER_LICENSE_NUMBER,
            PoliceOfficerCliAdapterTest::PENALTY_SERIES,
            PoliceOfficerCliAdapterTest::PENALTY_NUMBER,
            PoliceOfficerCliAdapterTest::PENALTY_POINTS,
            $isPaidOnSpot,
        );

        $client = self::createClient();
        self::getContainer()->set(ImposePenalty::class, $policeOfficer);

        $client->request(
            method: 'POST',
            uri: sprintf(
                'drivers/%s/penalties/%s/series/%s/number/%u/points/%u',
                urlencode(PoliceOfficerCliAdapterTest::DRIVER_LICENSE_NUMBER),
                $isPaidOnSpot ? 'paid-on-spot' : 'unpaid',
                urlencode(PoliceOfficerCliAdapterTest::PENALTY_SERIES),
                PoliceOfficerCliAdapterTest::PENALTY_NUMBER,
                PoliceOfficerCliAdapterTest::PENALTY_POINTS,
            ),
        );
        $response = $client->getResponse();

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertEmpty($response->getContent());
    }

    public static function provideIsPaidOnSpot(): \Generator
    {
        yield 'unpaid' => [false];

        yield 'paid on spot' => [true];
    }

    #[DataProvider('provideExceptions')]
    public function testOutputsExceptionsAsBadResponse(\Throwable $exception): void
    {
        $policeOfficer = $this->createStub(ImposePenalty::class);
        $policeOfficer->method('impose')->willThrowException($exception);

        $client = self::createClient();
        self::getContainer()->set(ImposePenalty::class, $policeOfficer);

        $client->request(
            method: 'POST',
            uri: sprintf(
                'drivers/%s/penalties/unpaid/series/%s/number/%u/points/%u',
                urlencode(PoliceOfficerCliAdapterTest::DRIVER_LICENSE_NUMBER),
                urlencode(PoliceOfficerCliAdapterTest::PENALTY_SERIES),
                PoliceOfficerCliAdapterTest::PENALTY_NUMBER,
                PoliceOfficerCliAdapterTest::PENALTY_POINTS,
            ),
        );
        $response = $client->getResponse();

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertEmpty($response->getContent());
    }

    public static function provideExceptions(): \Generator
    {
        yield PoliceOfficerCliAdapterTest::provideExceptions();
    }
}
