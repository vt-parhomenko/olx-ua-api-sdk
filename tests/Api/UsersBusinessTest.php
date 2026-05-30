<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests\Api;

use GuzzleHttp\Psr7\Response;
use Parhomenko\Olx\Api\UsersBusiness;
use Parhomenko\Olx\Tests\OlxTestCase;

class UsersBusinessTest extends OlxTestCase
{
    public function testMeReturnsProfile(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'name' => 'Shop' ] ]) ]);
        $business = new UsersBusiness($this->authenticator($client), $client);

        $result = $business->me();

        $this->assertSame([ 'name' => 'Shop' ], $result);

        $request = $this->history[0]['request'];
        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('/api/partner/users-business/me', $request->getUri()->getPath());
    }

    public function testUpdatePutsJson(): void
    {
        $client = $this->mockClient([ $this->jsonResponse(200, [ 'data' => [ 'name' => 'New' ] ]) ]);
        $business = new UsersBusiness($this->authenticator($client), $client);

        $business->update([ 'name' => 'New' ]);

        $request = $this->history[0]['request'];
        $this->assertSame('PUT', $request->getMethod());
        $this->assertSame('/api/partner/users-business/me', $request->getUri()->getPath());
        $this->assertSame([ 'name' => 'New' ], json_decode((string) $request->getBody(), true));
    }

    public function testLogosAndBannersUseDedicatedPaths(): void
    {
        $client = $this->mockClient([
            $this->jsonResponse(200, [ 'data' => [] ]),
            $this->jsonResponse(200, [ 'data' => [] ]),
        ]);
        $business = new UsersBusiness($this->authenticator($client), $client);

        $business->logos();
        $business->banners();

        $this->assertSame('/api/partner/users-business/me/logos', $this->history[0]['request']->getUri()->getPath());
        $this->assertSame('/api/partner/users-business/me/banners', $this->history[1]['request']->getUri()->getPath());
    }

    public function testDeleteLogoSendsDelete(): void
    {
        $client = $this->mockClient([ new Response(204) ]);
        $business = new UsersBusiness($this->authenticator($client), $client);

        $business->deleteLogo(3);

        $request = $this->history[0]['request'];
        $this->assertSame('DELETE', $request->getMethod());
        $this->assertSame('/api/partner/users-business/me/logos/3', $request->getUri()->getPath());
    }
}
