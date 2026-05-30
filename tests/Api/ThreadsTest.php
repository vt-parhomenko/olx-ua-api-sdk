<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests\Api;

use GuzzleHttp\Psr7\Response;
use Parhomenko\Olx\Api\Threads;
use Parhomenko\Olx\Exceptions\BadRequestException;
use Parhomenko\Olx\Tests\OlxTestCase;

class ThreadsTest extends OlxTestCase
{
    public function testGetReturnsDataEnvelope(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'id' => 42, 'subject' => 'Hi' ] ]) ]);
        $threads = new Threads($this->authenticator($client), $client);

        $result = $threads->get(42);

        $this->assertSame([ 'id' => 42, 'subject' => 'Hi' ], $result);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/partner/threads/42', $request->getUri()->getPath());
        $this->assertSame('Bearer test-token', $request->getHeaderLine('Authorization'));
        $this->assertSame('2.0', $request->getHeaderLine('Version'));
    }

    public function testGetAllSendsPaginationByDefault(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $threads = new Threads($this->authenticator($client), $client);

        $threads->getAll(5, 20);

        $query = $this->history[0]['request']->getUri()->getQuery();
        $this->assertStringContainsString('offset=5', $query);
        $this->assertStringContainsString('limit=20', $query);
        $this->assertStringNotContainsString('advert_id', $query);
        $this->assertStringNotContainsString('interlocutor_id', $query);
    }

    public function testGetAllSendsOptionalFilters(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $threads = new Threads($this->authenticator($client), $client);

        $threads->getAll(0, null, 100, 200);

        $query = $this->history[0]['request']->getUri()->getQuery();
        $this->assertStringContainsString('advert_id=100', $query);
        $this->assertStringContainsString('interlocutor_id=200', $query);
    }

    public function testMarkAsReadSendsCommand(): void
    {
        $client = $this->mockClient([ new Response(204) ]);
        $threads = new Threads($this->authenticator($client), $client);

        $threads->markAsRead(42);

        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/partner/threads/42/commands', $request->getUri()->getPath());
        $this->assertSame([ 'command' => 'mark-as-read' ], json_decode((string) $request->getBody(), true));
    }

    public function testSetFavouriteSendsTrueFlag(): void
    {
        $client = $this->mockClient([ new Response(204) ]);
        $threads = new Threads($this->authenticator($client), $client);

        $threads->setFavourite(42);

        $this->assertSame(
            [ 'command' => 'set-favourite', 'set-favourite' => true ],
            json_decode((string) $this->history[0]['request']->getBody(), true)
        );
    }

    public function testUnsetFavouriteSendsFalseFlag(): void
    {
        $client = $this->mockClient([ new Response(204) ]);
        $threads = new Threads($this->authenticator($client), $client);

        $threads->unsetFavourite(42);

        $this->assertSame(
            [ 'command' => 'set-favourite', 'set-favourite' => false ],
            json_decode((string) $this->history[0]['request']->getBody(), true)
        );
    }

    public function testPostSendsMessageTextAndExpects200(): void
    {
        $client = $this->mockClient([ new Response(200) ]);
        $threads = new Threads($this->authenticator($client), $client);

        $threads->post(42, 'Hello there');

        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/partner/threads/42/messages', $request->getUri()->getPath());
        $this->assertSame([ 'text' => 'Hello there' ], json_decode((string) $request->getBody(), true));
    }

    public function testPostThrowsWhenStatusIsNot200(): void
    {
        $client = $this->mockClient([ new Response(204) ]);
        $threads = new Threads($this->authenticator($client), $client);

        $this->expectException(BadRequestException::class);
        $threads->post(42, 'Hello there');
    }
}
