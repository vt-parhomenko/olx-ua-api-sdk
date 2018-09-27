<?php
namespace Parhomenko\Olx;

class OlxFactory{

    public static function get( $country_code, array $credentials, bool $update_token = false ) {

        $countries = [
            'ua' => '\\Parhomenko\\Olx\\Ua\\Api'
        ];

        if ( key_exists( strtolower($country_code), $countries) ){
            return new $countries[$country_code]( $credentials, $update_token );
        }

        throw new \Exception( "Country does not supported" );
    }

}