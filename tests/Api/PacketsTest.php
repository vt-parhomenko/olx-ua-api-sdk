<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests\Api;

use GuzzleHttp\Psr7\Response;
use Parhomenko\Olx\Api\Packets;
use Parhomenko\Olx\Tests\OlxTestCase;

class PacketsTest extends OlxTestCase
{
    public function testGetAllSendsRequiredAndOptionalQuery(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $packets = new Packets($this->authenticator($client), $client);

        $packets->getAll(1808, 'account', 'base', true, 5);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/partner/packets', $request->getUri()->getPath());

        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('category_id=1808', $query);
        $this->assertStringContainsString('payment_method=account', $query);
        $this->assertStringContainsString('type=base', $query);
        $this->assertStringContainsString('with_features=1', $query);
        $this->assertStringContainsString('zone_id=5', $query);
    }

    public function testGetAllOmitsUnsetOptionalParams(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $packets = new Packets($this->authenticator($client), $client);

        $packets->getAll(1808, 'postpaid');

        $query = $this->history[0]['request']->getUri()->getQuery();
        $this->assertStringContainsString('category_id=1808', $query);
        $this->assertStringContainsString('payment_method=postpaid', $query);
        $this->assertStringNotContainsString('type=', $query);
        $this->assertStringNotContainsString('with_features', $query);
        $this->assertStringNotContainsString('zone_id', $query);
    }

    public function testZonesSendsCategory(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $packets = new Packets($this->authenticator($client), $client);

        $packets->zones(1808);

        $request = $this->history[0]['request'];
        $this->assertSame('/api/partner/zones', $request->getUri()->getPath());
        $this->assertStringContainsString('category_id=1808', $request->getUri()->getQuery());
    }

    public function testUserPacketsSendsPaginationAndFilters(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $packets = new Packets($this->authenticator($client), $client);

        $packets->userPackets(10, 20, 'active', 'expired_at');

        $request = $this->history[0]['request'];
        $this->assertSame('/api/partner/users/me/packets', $request->getUri()->getPath());

        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('offset=10', $query);
        $this->assertStringContainsString('limit=20', $query);
        $this->assertStringContainsString('availability=active', $query);
        $this->assertStringContainsString('sort_by=expired_at', $query);
    }

    public function testBuyForUserPostsJson(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'id' => 1 ] ]) ]);
        $packets = new Packets($this->authenticator($client), $client);

        $result = $packets->buyForUser([ 'category_id' => 1808, 'payment_method' => 'account', 'size' => 10 ]);

        $this->assertSame([ 'id' => 1 ], $result);

        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/partner/users/me/packets', $request->getUri()->getPath());
        $this->assertSame([ 'category_id' => 1808, 'payment_method' => 'account', 'size' => 10 ], json_decode((string) $request->getBody(), true));
    }

    public function testBuyForAdvertPostsToAdvertPackets(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'id' => 2 ] ]) ]);
        $packets = new Packets($this->authenticator($client), $client);

        $packets->buyForAdvert(55, [ 'payment_method' => 'account' ]);

        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/partner/adverts/55/packets', $request->getUri()->getPath());
        $this->assertSame([ 'payment_method' => 'account' ], json_decode((string) $request->getBody(), true));
    }
}
