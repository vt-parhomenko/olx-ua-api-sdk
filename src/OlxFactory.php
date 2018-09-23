<?php
namespace Parhomenko\Olx;

class OlxFactory{

    public static function get( $country_code, array $credentials ) {

        $countries = [
            'ua' => '\\Parhomenko\\Olx\\Ua\\Api'
        ];

        if ( key_exists( strtolower($country_code), $countries) ){
            return new $countries[$country_code]( $credentials );
        }

        throw new \Exception( "Country does not supported" );
    }

}