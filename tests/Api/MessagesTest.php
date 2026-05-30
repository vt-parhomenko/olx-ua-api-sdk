<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests\Api;

use Parhomenko\Olx\Api\Messages;
use Parhomenko\Olx\Tests\OlxTestCase;

class MessagesTest extends OlxTestCase
{
    public function testGetReturnsDataEnvelope(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ [ 'id' => 1, 'text' => 'Hi' ] ] ]) ]);
        $messages = new Messages($this->authenticator($client), $client);

        $result = $messages->get(42);

        $this->assertSame([ [ 'id' => 1, 'text' => 'Hi' ] ], $result);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/partner/threads/42/messages', $request->getUri()->getPath());
        $this->assertSame('Bearer test-token', $request->getHeaderLine('Authorization'));
        $this->assertSame('2.0', $request->getHeaderLine('Version'));
    }

    public function testGetSendsPaginationQuery(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $messages = new Messages($this->authenticator($client), $client);

        $messages->get(42, 10, 25);

        $query = $this->history[0]['request']->getUri()->getQuery();
        $this->assertStringContainsString('offset=10', $query);
        $this->assertStringContainsString('limit=25', $query);
    }

    public function testGetOneReturnsSingleMessage(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'id' => 9, 'text' => 'Hi' ] ]) ]);
        $messages = new Messages($this->authenticator($client), $client);

        $result = $messages->getOne(42, 9);

        $this->assertSame([ 'id' => 9, 'text' => 'Hi' ], $result);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/partner/threads/42/messages/9', $request->getUri()->getPath());
    }
}
