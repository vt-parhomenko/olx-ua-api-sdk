<?php

namespace Parhomenko\Olx\Api;

use GuzzleHttp\Client;

class Currencies
{
    const API_VERSION = '2.0';
    const OLX_CURRENCY_URL = '/api/partner/currencies';

    private $user;
    private $guzzleClient;

    public function __construct( User $user, Client $guzzleClient )
    {
        $this->user = $user;
        $this->guzzleClient = $guzzleClient;
    }

    public function getAll(int $offset = 0, int $limit = null) : array
    {
        try {
            $response = $this->guzzleClient->request('GET', self::OLX_CURRENCY_URL, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ],
                'query' => [
                    'offset' => $offset,
                    'limit' => $limit
                ]
            ]);

            $cities = json_decode( $response->getBody()->getContents(), true );

            if( !isset( $cities['data'] ) )
                throw new \Exception( 'Got empty response | Get all OLX currencies' );

            return $cities['data'];

        } catch ( \Exception $e ){
            throw $e;
        }
    }
}