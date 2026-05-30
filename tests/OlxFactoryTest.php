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

    public function testThrowsForUnknownCountry(): void
    {
        $this->expectException(UnknownCountryException::class);

        OlxFactory::get('xx', [
            'client_id' => 'id',
            'client_secret' => 'secret',
        ]);
    }
}
