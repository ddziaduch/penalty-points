<?php

declare(strict_types=1);

namespace ddziaduch\PenaltyPoints\Tests\Adapters\Primary\Http;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\ImposePenalty;
use ddziaduch\PenaltyPoints\Tests\Adapters\Primary\Cli\ImposePenaltyCliAdapterTest;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \ddziaduch\PenaltyPoints\Adapters\Primary\Http\ImposePenaltyHttpAdapter
 *
 * @internal
 */
final class ImposePenaltyHttpAdapterTest extends WebTestCase
{
    #[DataProvider('provideIsPaidOnSpot')]
    public function testPassesArgumentsToThePort(bool $isPaidOnSpot): void
    {
        $imposePenalty = $this->createMock(ImposePenalty::class);
        $imposePenalty->expects(self::once())->method('impose')->with(
            ImposePenaltyCliAdapterTest::DRIVER_LICENSE_NUMBER,
            ImposePenaltyCliAdapterTest::PENALTY_SERIES,
            ImposePenaltyCliAdapterTest::PENALTY_NUMBER,
            ImposePenaltyCliAdapterTest::PENALTY_POINTS,
            $isPaidOnSpot,
        );

        $client = self::createClient();
        self::getContainer()->set(ImposePenalty::class, $imposePenalty);

        $client->request(
            method: 'POST',
            uri: sprintf(
                'drivers/%s/penalties/%s/series/%s/number/%u/points/%u',
                urlencode(ImposePenaltyCliAdapterTest::DRIVER_LICENSE_NUMBER),
                $isPaidOnSpot ? 'paid-on-spot' : 'unpaid',
                urlencode(ImposePenaltyCliAdapterTest::PENALTY_SERIES),
                ImposePenaltyCliAdapterTest::PENALTY_NUMBER,
                ImposePenaltyCliAdapterTest::PENALTY_POINTS,
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
        $imposePenalty = $this->createStub(ImposePenalty::class);
        $imposePenalty->method('impose')->willThrowException($exception);

        $client = self::createClient();
        self::getContainer()->set(ImposePenalty::class, $imposePenalty);

        $client->request(
            method: 'POST',
            uri: sprintf(
                'drivers/%s/penalties/unpaid/series/%s/number/%u/points/%u',
                urlencode(ImposePenaltyCliAdapterTest::DRIVER_LICENSE_NUMBER),
                urlencode(ImposePenaltyCliAdapterTest::PENALTY_SERIES),
                ImposePenaltyCliAdapterTest::PENALTY_NUMBER,
                ImposePenaltyCliAdapterTest::PENALTY_POINTS,
            ),
        );
        $response = $client->getResponse();

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public static function provideExceptions(): \Generator
    {
        return ImposePenaltyCliAdapterTest::provideExceptions();
    }
}
