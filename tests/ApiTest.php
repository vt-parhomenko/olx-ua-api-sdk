<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Parhomenko\Olx\Api;
use Parhomenko\Olx\Api\Adverts;
use Parhomenko\Olx\Api\Locations;
use Parhomenko\Olx\Api\Messages;
use Parhomenko\Olx\Api\Packets;
use Parhomenko\Olx\Api\PaidFeatures;
use Parhomenko\Olx\Api\Threads;
use Parhomenko\Olx\Api\UsersBusiness;

class ApiTest extends OlxTestCase
{
    /**
     * @param array<string, mixed> $httpOptions
     * @throws Exception
     */
    private function api(array $httpOptions = []): Api
    {
        return new Api('https://www.olx.ua/', [
            'client_id' => 'id',
            'client_secret' => 'secret',
        ], false, $httpOptions);
    }

    /**
     * @throws Exception
     */
    public function testAccessorsReturnExpectedResources(): void
    {
        $api = $this->api();

        $this->assertInstanceOf(Adverts::class, $api->adverts());
        $this->assertInstanceOf(Threads::class, $api->threads());
        $this->assertInstanceOf(Messages::class, $api->messages());
        $this->assertInstanceOf(Packets::class, $api->packets());
        $this->assertInstanceOf(PaidFeatures::class, $api->paidFeatures());
        $this->assertInstanceOf(Locations::class, $api->locations());
        $this->assertInstanceOf(UsersBusiness::class, $api->usersBusiness());
    }

    /**
     * @throws Exception
     */
    public function testResourcesAreMemoized(): void
    {
        $api = $this->api();

        $this->assertSame($api->adverts(), $api->adverts());
        $this->assertSame($api->categories(), $api->categories());
        $this->assertSame($api->threads(), $api->threads());
        $this->assertSame($api->packets(), $api->packets());
        $this->assertSame($api->paidFeatures(), $api->paidFeatures());
        $this->assertSame($api->locations(), $api->locations());
        $this->assertSame($api->usersBusiness(), $api->usersBusiness());
    }

    /**
     * @throws Exception
     */
    public function testDefaultClientKeepsGuzzleDefaults(): void
    {
        $client = $this->api()->getHttpClient();

        $this->assertInstanceOf(Client::class, $client);
        $this->assertSame('https://www.olx.ua/', (string) $client->getConfig('base_uri'));
        $this->assertNull($client->getConfig('timeout'));
        $this->assertNull($client->getConfig('connect_timeout'));
    }

    /**
     * @throws Exception
     */
    public function testHttpOptionsAreAppliedToTheClient(): void
    {
        $client = $this->api([ 'timeout' => 7, 'connect_timeout' => 3 ])->getHttpClient();

        $this->assertSame(7, $client->getConfig('timeout'));
        $this->assertSame(3, $client->getConfig('connect_timeout'));
    }

    /**
     * @throws Exception
     */
    public function testBaseUriArgumentWinsOverHttpOptions(): void
    {
        $client = $this->api([ 'base_uri' => 'https://example.test/' ])->getHttpClient();

        $this->assertSame('https://www.olx.ua/', (string) $client->getConfig('base_uri'));
    }

    /**
     * @throws Exception
     */
    public function testCustomHandlerServesTokenAndResourceRequests(): void
    {
        $history = [];
        $stack = HandlerStack::create(new MockHandler([
            $this->jsonResponse(200, [
                'access_token' => 'fresh-token',
                'token_type' => 'Bearer',
                'expires_in' => 86400,
                'refresh_token' => 'fresh-refresh',
            ]),
            $this->jsonResponse(200, [ 'data' => [ 'id' => 1 ] ]),
        ]));
        $stack->push(Middleware::history($history));

        // update_token = true: no stored access token, so the constructor refreshes it
        $api = new Api('https://www.olx.ua/', [
            'client_id' => 'id',
            'client_secret' => 'secret',
            'refresh_token' => 'stored-refresh',
        ], true, [ 'handler' => $stack, 'timeout' => 7, 'connect_timeout' => 3 ]);

        $this->assertSame([ 'id' => 1 ], $api->adverts()->get(1));
        $this->assertCount(2, $history);

        $tokenRequest = $history[0]['request'];
        $this->assertSame('POST', $tokenRequest->getMethod());
        $this->assertSame('/api/open/oauth/token', $tokenRequest->getUri()->getPath());

        $advertRequest = $history[1]['request'];
        $this->assertSame('www.olx.ua', $advertRequest->getUri()->getHost());
        $this->assertSame('/api/partner/adverts/1', $advertRequest->getUri()->getPath());
        $this->assertSame('Bearer fresh-token', $advertRequest->getHeaderLine('Authorization'));

        foreach ($history as $transaction) {
            $this->assertSame(7, $transaction['options']['timeout']);
            $this->assertSame(3, $transaction['options']['connect_timeout']);
        }
    }

    public function testResourcesAcceptAnyClientInterface(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('request')
            ->with('GET', '/api/partner/adverts/5', $this->anything())
            ->willReturn(new Response(200, [], (string) json_encode([ 'data' => [ 'id' => 5 ] ])));

        $adverts = new Adverts($this->authenticator($client), $client);

        $this->assertSame([ 'id' => 5 ], $adverts->get(5));
    }
}
