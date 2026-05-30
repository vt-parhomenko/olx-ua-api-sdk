<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests;

use Parhomenko\Olx\Country;
use PHPUnit\Framework\TestCase;

class CountryTest extends TestCase
{
    public function testBaseUriForEachCase(): void
    {
        $this->assertSame('https://www.olx.ua/', Country::UA->baseUri());
        $this->assertSame('https://www.olx.pl/', Country::PL->baseUri());
        $this->assertSame('https://www.olx.bg/', Country::BG->baseUri());
        $this->assertSame('https://www.olx.ro/', Country::RO->baseUri());
        $this->assertSame('https://www.olx.kz/', Country::KZ->baseUri());
        $this->assertSame('https://www.olx.pt/', Country::PT->baseUri());
    }

    public function testTryFromAcceptsKnownCode(): void
    {
        $this->assertSame(Country::UA, Country::tryFrom('ua'));
    }

    public function testTryFromReturnsNullForUnknownCode(): void
    {
        $this->assertNull(Country::tryFrom('xx'));
    }
}
