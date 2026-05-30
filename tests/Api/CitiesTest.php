<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests\Api;

use Parhomenko\Olx\Api\Cities;
use Parhomenko\Olx\Tests\OlxTestCase;

class CitiesTest extends OlxTestCase
{
    public function testDistrictsPath(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $cities = new Cities($this->authenticator($client), $client);

        $cities->districts(5);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/partner/cities/5/districts', $request->getUri()->getPath());
    }

    public function testGetAllSendsPagination(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $cities = new Cities($this->authenticator($client), $client);

        $cities->getAll(5, 20);

        $query = $this->history[0]['request']->getUri()->getQuery();
        $this->assertStringContainsString('offset=5', $query);
        $this->assertStringContainsString('limit=20', $query);
    }
}
