<?php

declare(strict_types=1);

namespace Parhomenko\Olx;

enum Country: string
{
    case UA = 'ua';
    case PL = 'pl';
    case BG = 'bg';
    case RO = 'ro';
    case KZ = 'kz';
    case PT = 'pt';

    /**
     * Base API URI of the country's OLX site.
     */
    public function baseUri(): string
    {
        return match ($this) {
            self::UA => 'https://www.olx.ua/',
            self::PL => 'https://www.olx.pl/',
            self::BG => 'https://www.olx.bg/',
            self::RO => 'https://www.olx.ro/',
            self::KZ => 'https://www.olx.kz/',
            self::PT => 'https://www.olx.pt/',
        };
    }
}
