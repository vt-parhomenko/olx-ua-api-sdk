<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests\Api;

use GuzzleHttp\Psr7\Response;
use Parhomenko\Olx\Api\Adverts;
use Parhomenko\Olx\Exceptions\BadRequestException;
use Parhomenko\Olx\Exceptions\NotFoundException;
use Parhomenko\Olx\Tests\OlxTestCase;

class AdvertsTest extends OlxTestCase
{
    public function testGetReturnsDataEnvelope(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'id' => 1, 'title' => 'Car' ] ]) ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $result = $adverts->get(1);

        $this->assertSame([ 'id' => 1, 'title' => 'Car' ], $result);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/partner/adverts/1', $request->getUri()->getPath());
        $this->assertSame('Bearer test-token', $request->getHeaderLine('Authorization'));
        $this->assertSame('2.0', $request->getHeaderLine('Version'));
    }

    public function testGetAllSendsQueryParameters(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $adverts->getAll(10, 50, 'ext-1', '1808');

        $query = $this->history[0]['request']->getUri()->getQuery();
        $this->assertStringContainsString('offset=10', $query);
        $this->assertStringContainsString('limit=50', $query);
        $this->assertStringContainsString('external_id=ext-1', $query);
        $this->assertStringContainsString('category_ids=1808', $query);
    }

    public function testCreateSendsJsonBody(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'id' => 7 ] ]) ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $adverts->create([ 'title' => 'New advert' ]);

        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame([ 'title' => 'New advert' ], json_decode((string) $request->getBody(), true));
    }

    public function testEmptyResponseThrowsBadRequest(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'meta' => [] ]) ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $this->expectException(BadRequestException::class);
        $adverts->get(1);
    }

    public function testMalformedJsonThrowsBadRequest(): void
    {
        $client = $this->mockClient([ new Response(200, [ 'Content-Type' => 'application/json' ], 'not valid json{') ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $this->expectException(BadRequestException::class);
        $adverts->get(1);
    }

    public function testActivateSendsCommand(): void
    {
        $client = $this->mockClient([ new Response(204) ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $adverts->activate(1);

        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/partner/adverts/1/commands', $request->getUri()->getPath());
        $this->assertSame([ 'command' => 'activate' ], json_decode((string) $request->getBody(), true));
    }

    public function testHttpErrorIsMappedToTypedException(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(404, [ 'error' => [ 'title' => 'Not found', 'detail' => 'No advert' ] ]) ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $this->expectException(NotFoundException::class);
        $adverts->get(999);
    }

    public function testDeleteSendsSingleDeleteRequest(): void
    {
        $client = $this->mockClient([ new Response(204) ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $adverts->delete(5);

        $this->assertCount(1, $this->history);
        $this->assertSame('DELETE', $this->history[0]['request']->getMethod());
        $this->assertSame('/api/partner/adverts/5', $this->history[0]['request']->getUri()->getPath());
    }

    public function testUpdateSendsPutWithJsonBody(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'id' => 5, 'title' => 'Updated' ] ]) ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $result = $adverts->update(5, [ 'title' => 'Updated' ]);

        $this->assertSame([ 'id' => 5, 'title' => 'Updated' ], $result);

        $request = $this->history[0]['request'];
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/api/partner/adverts/5', $request->getUri()->getPath());
        $this->assertSame([ 'title' => 'Updated' ], json_decode((string) $request->getBody(), true));
    }

    public function testDeactivateSendsCommandWithSuccessFlag(): void
    {
        $client = $this->mockClient([ new Response(204) ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $adverts->deactivate(1);

        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/partner/adverts/1/commands', $request->getUri()->getPath());
        $this->assertSame(
            [ 'command' => 'deactivate', 'is_success' => true ],
            json_decode((string) $request->getBody(), true)
        );
    }

    public function testDeactivateCanSignalUnsuccessfulDeal(): void
    {
        $client = $this->mockClient([ new Response(204) ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $adverts->deactivate(1, false);

        $this->assertSame(
            [ 'command' => 'deactivate', 'is_success' => false ],
            json_decode((string) $this->history[0]['request']->getBody(), true)
        );
    }

    public function testStatisticsPath(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'views' => 10 ] ]) ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $result = $adverts->statistics(5);

        $this->assertSame([ 'views' => 10 ], $result);
        $this->assertSame('/api/partner/adverts/5/statistics', $this->history[0]['request']->getUri()->getPath());
    }

    public function testDeleteStatisticSendsDelete(): void
    {
        $client = $this->mockClient([ new Response(204) ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $adverts->deleteStatistic(5, 'phone_views');

        $request = $this->history[0]['request'];
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/api/partner/adverts/5/statistics/phone_views', $request->getUri()->getPath());
    }

    public function testModerationReasonPath(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'reason' => 'duplicate' ] ]) ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $result = $adverts->moderationReason(5);

        $this->assertSame([ 'reason' => 'duplicate' ], $result);
        $this->assertSame('/api/partner/adverts/5/moderation-reason', $this->history[0]['request']->getUri()->getPath());
    }

    public function testLogosLifecycle(): void
    {
        $client = $this->mockClient([
            $this->jsonResponse(200, [ 'data' => [] ]),
            $this->jsonResponse(200, [ 'data' => [ 'id' => 3 ] ]),
            new Response(204),
        ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        $adverts->logos(5);
        $adverts->addLogo(5, [ 'url' => 'http://x/logo.png' ]);
        $adverts->deleteLogo(5, 3);

        $this->assertSame('GET', $this->history[0]['request']->getMethod());
        $this->assertSame('/api/partner/adverts/5/logos', $this->history[0]['request']->getUri()->getPath());
        $this->assertSame('POST', $this->history[1]['request']->getMethod());
        $this->assertSame('/api/partner/adverts/5/logos', $this->history[1]['request']->getUri()->getPath());
        $this->assertSame('DELETE', $this->history[2]['request']->getMethod());
        $this->assertSame('/api/partner/adverts/5/logos/3', $this->history[2]['request']->getUri()->getPath());
    }

    public function testGetAllKeepsZeroStringExternalId(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $adverts = new Adverts($this->authenticator($client), $client);

        // '0' is a legitimate partner-assigned external_id and must not be dropped.
        $adverts->getAll(0, null, '0');

        $this->assertStringContainsString('external_id=0', $this->history[0]['request']->getUri()->getQuery());
    }
}
