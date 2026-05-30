<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests\Api;

use Parhomenko\Olx\Api\Locations;
use Parhomenko\Olx\Tests\OlxTestCase;

class LocationsTest extends OlxTestCase
{
    public function testGetByCoordinatesSendsLatLng(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [] ]) ]);
        $locations = new Locations($this->authenticator($client), $client);

        $locations->getByCoordinates(50.45, 30.52);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/partner/locations', $request->getUri()->getPath());

        $query = $request->getUri()->getQuery();
        $this->assertStringContainsString('latitude=50.45', $query);
        $this->assertStringContainsString('longitude=30.52', $query);
    }
}
