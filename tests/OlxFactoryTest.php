<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests;

use Parhomenko\Olx\Api;
use Parhomenko\Olx\Country;
use Parhomenko\Olx\Exceptions\UnknownCountryException;
use Parhomenko\Olx\OlxFactory;
use PHPUnit\Framework\TestCase;

class OlxFactoryTest extends TestCase
{
    public function testReturnsApiForSupportedCountryCode(): void
    {
        $api = OlxFactory::get('ua', [
            'client_id' => 'id',
            'client_secret' => 'secret',
        ]);

        $this->assertInstanceOf(Api::class, $api);
    }

    public function testReturnsApiForCountryEnum(): void
    {
        $api = OlxFactory::get(Country::UA, [
            'client_id' => 'id',
            'client_secret' => 'secret',
        ]);

        $this->assertInstanceOf(Api::class, $api);
    }

    public function testPassesHttpOptionsToTheClient(): void
    {
        $api = OlxFactory::get(Country::PL, [
            'client_id' => 'id',
            'client_secret' => 'secret',
        ], false, [ 'timeout' => 7, 'connect_timeout' => 3 ]);

        $client = $api->getHttpClient();

        $this->assertSame(7, $client->getConfig('timeout'));
        $this->assertSame(3, $client->getConfig('connect_timeout'));
        $this->assertSame(Country::PL->baseUri(), (string) $client->getConfig('base_uri'));
    }

    public function testHttpOptionsDefaultToNone(): void
    {
        $api = OlxFactory::get('ua', [
            'client_id' => 'id',
            'client_secret' => 'secret',
        ]);

        $this->assertNull($api->getHttpClient()->getConfig('timeout'));
    }

    public function testThrowsForUnknownCountry(): void
    {
        $this->expectException(UnknownCountryException::class);

        OlxFactory::get('xx', [
            'client_id' => 'id',
            'client_secret' => 'secret',
        ]);
    }
}
