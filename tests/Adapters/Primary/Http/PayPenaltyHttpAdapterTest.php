<?php

declare(strict_types=1);

namespace Adapters\Primary\Http;

use ddziaduch\PenaltyPoints\Application\Ports\Primary\PayPenalty;
use ddziaduch\PenaltyPoints\Tests\Adapters\Primary\Cli\ImposePenaltyCliAdapterTest;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @covers \ddziaduch\PenaltyPoints\Adapters\Primary\Http\PayPenaltyHttpAdapter
 *
 * @internal
 */
final class PayPenaltyHttpAdapterTest extends WebTestCase
{
    public function testPassesArgumentsToThePort(): void
    {
        $payPenalty = $this->createMock(PayPenalty::class);
        $payPenalty->expects(self::once())->method('pay')->with(
            ImposePenaltyCliAdapterTest::DRIVER_LICENSE_NUMBER,
            ImposePenaltyCliAdapterTest::PENALTY_SERIES,
            ImposePenaltyCliAdapterTest::PENALTY_NUMBER,
        );

        $client = self::createClient();
        self::getContainer()->set(PayPenalty::class, $payPenalty);

        $client->request(
            method: 'POST',
            uri: sprintf(
                'drivers/%s/penalties/series/%s/number/%u/pay',
                urlencode(ImposePenaltyCliAdapterTest::DRIVER_LICENSE_NUMBER),
                urlencode(ImposePenaltyCliAdapterTest::PENALTY_SERIES),
                ImposePenaltyCliAdapterTest::PENALTY_NUMBER,
            ),
        );
        $response = $client->getResponse();

        self::assertSame(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        self::assertEmpty($response->getContent());
    }

    #[DataProvider('provideExceptions')]
    public function testOutputsExceptionsAsBadResponse(\Throwable $exception): void
    {
        $payPenalty = $this->createStub(PayPenalty::class);
        $payPenalty->method('pay')->willThrowException($exception);

        $client = self::createClient();
        self::getContainer()->set(PayPenalty::class, $payPenalty);

        $client->request(
            method: 'POST',
            uri: sprintf(
                'drivers/%s/penalties/series/%s/number/%u/pay',
                urlencode(ImposePenaltyCliAdapterTest::DRIVER_LICENSE_NUMBER),
                urlencode(ImposePenaltyCliAdapterTest::PENALTY_SERIES),
                ImposePenaltyCliAdapterTest::PENALTY_NUMBER,
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
