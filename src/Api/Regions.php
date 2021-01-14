<?php

namespace Parhomenko\Olx\Api;

use GuzzleHttp\Client;

class Regions
{
    const API_VERSION = '2.0';
    const OLX_REGIONS_URL = '/api/partner/regions';

    private $user;
    private $guzzleClient;

    public function __construct( User $user, Client $guzzleClient )
    {
        $this->user = $user;
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getAll() : array
    {
        try {
            $response = $this->guzzleClient->request('GET', self::OLX_REGIONS_URL, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ]
            ]);

            $regions = json_decode($response->getBody()->getContents(), true);

            if( !isset( $regions['data'] ) )
                throw new \Exception( 'Got empty response | Get all OLX regions' );

            return $regions['data'];

        } catch ( \Exception $e ){
            throw $e;
        }
    }

    /**
     * @param int $region_id
     * @return array
     * @throws \Exception
     */
    public function get( int $region_id ) : array
    {
        try {
            $response = $this->guzzleClient->request('GET', self::OLX_REGIONS_URL .'/' .$region_id, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if( !isset( $data['data'] ) ){
                throw new \Exception( 'Got empty response | Get all OLX regions' );
            }

            return $data['data'];

        } catch ( \Exception $e ){
            throw $e;
        }
    }
}