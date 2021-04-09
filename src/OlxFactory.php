<?php

namespace Parhomenko\Olx;

use Parhomenko\Olx\Exceptions\UnknownCountryException;

class OlxFactory {

    /**
     * @param $country_code
     * @param array $credentials
     * @param bool $update_token
     * @return Api
     * @throws UnknownCountryException
     */
    public static function get( $country_code, array $credentials, bool $update_token = false ): Api
    {
        $links = [
            'ua' => 'https://www.olx.ua/',
            'pl' => 'https://www.olx.pl/',
            'bg' => 'https://www.olx.bg/',
            'ro' => 'https://www.olx.ro/',
            'kz' => 'https://www.olx.kz/',
            'pt' => 'https://www.olx.pt/',
        ];

        if (array_key_exists($country_code, $links)) {
            return new Api($links[$country_code], $credentials, $update_token);
        }

        throw new UnknownCountryException( "Country does not supported" );
    }

}