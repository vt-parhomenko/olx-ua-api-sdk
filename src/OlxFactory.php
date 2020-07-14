<?php

namespace Parhomenko\Olx;

class OlxFactory {

    public static function get( $country_code, array $credentials, bool $update_token = false ) {
        $links = [
            'ua' => 'https://www.olx.ua/',
            'pl' => 'https://www.olx.pl/',
            'bg' => 'https://www.olx.bg/',
            'ro' => 'https://www.olx.ro/',
            'kz' => 'https://www.olx.kz/',
            'by' => 'https://www.olx.by/',
            'pt' => 'https://www.olx.pt/',
            'ao' => 'https://www.olx.co.ao/',
            'mz' => 'https://www.olx.co.mz/',
        ];

        if (key_exists($country_code, $links)) {
            return new Api($links[$country_code], $credentials, $update_token);
        }

        throw new \Exception( "Country does not supported" );
    }

}