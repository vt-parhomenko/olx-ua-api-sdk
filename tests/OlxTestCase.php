<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Parhomenko\Olx\Api\Authenticator;
use Parhomenko\Olx\Credentials;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class OlxTestCase extends BaseTestCase
{
    /**
     * Recorded request/response transactions of the last mock client.
     *
     * @var array<int, array>
     */
    protected $history = [];

    /**
     * Build a Guzzle client backed by a queue of canned responses.
     *
     * @param Response[] $responses
     */
    protected function mockClient(array $responses): Client
    {
        $this->history = [];

        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(Middleware::history($this->history));

        return new Client([
            'handler' => $stack,
            'base_uri' => 'https://www.olx.ua/',
        ]);
    }

    protected function authenticator(Client $client): Authenticator
    {
        return new Authenticator($client, new Credentials(
            clientId: 'test-id',
            clientSecret: 'test-secret',
            accessToken: 'test-token',
            tokenType: 'Bearer',
        ), 'https://www.olx.ua/');
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function jsonResponse(int $status, array $data): Response
    {
        return new Response($status, [ 'Content-Type' => 'application/json' ], json_encode($data));
    }
}
