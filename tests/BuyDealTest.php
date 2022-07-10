<?php

declare(strict_types=1);

namespace App\Tests;

use EnricoStahn\JsonAssert\Assert as JsonAssert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert as WebmozartAssert;

class BuyDealTest extends WebTestCase
{
    use JsonAssert;

    public const DEAL_BUY_ROUTE = 'deal/buy';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->client);
    }
    public function test_it_with_1(): void
    {
        $response = $this->jsonRequest(Request::METHOD_GET,self::DEAL_BUY_ROUTE.'/1');

        self::assertResponseIsSuccessful();
        self::assertSame($response,[250 => 1]);
    }
    public function test_it_with_250(): void
    {
        $response = $this->jsonRequest(Request::METHOD_GET,self::DEAL_BUY_ROUTE.'/250');

        self::assertResponseIsSuccessful();
        self::assertSame($response,[250 => 1]);
    }
    public function test_it_with_251(): void
    {
        $response = $this->jsonRequest(Request::METHOD_GET,self::DEAL_BUY_ROUTE.'/251');

        self::assertResponseIsSuccessful();
        self::assertSame($response,[500 => 1]);
    }
    public function test_it_with_501(): void
    {
        $response = $this->jsonRequest(Request::METHOD_GET,self::DEAL_BUY_ROUTE.'/501');

        self::assertResponseIsSuccessful();
        self::assertSame($response,[500 => 1, 250=>1]);
    }
    public function test_it_with_12001(): void
    {
        $response = $this->jsonRequest(Request::METHOD_GET,self::DEAL_BUY_ROUTE.'/12001');

        self::assertResponseIsSuccessful();
        self::assertSame($response,[5000 => 2,2000=>1,250=>1]);
    }
    protected function jsonRequest(string $method, string $route, array $bodyData = [], array $headerData = []): array
    {
        $this->client->jsonRequest($method, $route, parameters: $bodyData, server: $headerData, changeHistory: false);

        $response = $this->client->getResponse()->getContent();
        WebmozartAssert::notFalse($response);

        /**
         * @psalm-suppress MixedAssignment
         */
        $response = json_decode($response, true);
        self::assertIsArray($response);

        return $response;
    }

}
