<?php

namespace Parhomenko\Olx\Api;

use GuzzleHttp\Client;

class Cities
{
    const API_VERSION = '2.0';
    const OLX_CITIES_URL = '/api/partner/cities';

    private $user;
    private $guzzleClient;

    public function __construct( User $user, Client $guzzleClient )
    {
        $this->user = $user;
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @param int $offset
     * @param int|null $limit
     * @return array
     * @throws \Exception
     */
    public function getAll(int $offset = 0, int $limit = null) : array
    {
        try {
            $response = $this->guzzleClient->request('GET', self::OLX_CITIES_URL, [
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

            if( !isset( $cities['data'] ) ) throw new \Exception( 'Got empty response | Get all OLX cities' );

            return $cities['data'];

        }catch( \Exception $e ){
            throw $e;
        }
    }

    /**
     * @param int $city_id
     * @return array
     * @throws \Exception
     */
    public function get(int $city_id) : array
    {
        try {
            $response = $this->guzzleClient->request('GET', self::OLX_CITIES_URL .'/' .$city_id, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ]
            ]);

            $data = json_decode( $response->getBody()->getContents(), true );

            if( !isset( $data['data'] ) ) throw new \Exception( 'Got empty response | Get all OLX cities' );

            return $data['data'];

        }catch( \Exception $e ){
            throw $e;
        }
    }
}