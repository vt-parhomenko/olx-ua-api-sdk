<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests\Api;

use Parhomenko\Olx\Api\PaidFeatures;
use Parhomenko\Olx\Tests\OlxTestCase;

class PaidFeaturesTest extends OlxTestCase
{
    public function testGetAllReturnsData(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ [ 'code' => 'promote' ] ] ]) ]);
        $features = new PaidFeatures($this->authenticator($client), $client);

        $result = $features->getAll();

        $this->assertSame([ [ 'code' => 'promote' ] ], $result);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/partner/paid-features', $request->getUri()->getPath());
    }

    public function testForAdvertPath(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $features = new PaidFeatures($this->authenticator($client), $client);

        $features->forAdvert(7);

        $this->assertSame('/api/partner/adverts/7/paid-features', $this->history[0]['request']->getUri()->getPath());
    }

    public function testBuyForAdvertPostsJson(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'id' => 9 ] ]) ]);
        $features = new PaidFeatures($this->authenticator($client), $client);

        $features->buyForAdvert(7, [ 'code' => 'promote', 'payment_method' => 'account' ]);

        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/partner/adverts/7/paid-features', $request->getUri()->getPath());
        $this->assertSame([ 'code' => 'promote', 'payment_method' => 'account' ], json_decode((string) $request->getBody(), true));
    }
}
