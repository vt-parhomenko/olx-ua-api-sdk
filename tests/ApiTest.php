<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests;

use Exception;
use Parhomenko\Olx\Api;
use Parhomenko\Olx\Api\Adverts;
use Parhomenko\Olx\Api\Locations;
use Parhomenko\Olx\Api\Messages;
use Parhomenko\Olx\Api\Packets;
use Parhomenko\Olx\Api\PaidFeatures;
use Parhomenko\Olx\Api\Threads;
use Parhomenko\Olx\Api\UsersBusiness;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    /**
     * @throws Exception
     */
    private function api(): Api
    {
        return new Api('https://www.olx.ua/', [
            'client_id' => 'id',
            'client_secret' => 'secret',
        ]);
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
}
