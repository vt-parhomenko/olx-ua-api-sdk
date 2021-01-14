<?php

namespace Parhomenko\Olx\Api;

use GuzzleHttp\Client;

class Districts
{
    const API_VERSION = '2.0';
    const OLX_DISTRICTS_URL = '/api/partner/districts';

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
            $response = $this->guzzleClient->request('GET', self::OLX_DISTRICTS_URL, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ]
            ]);

            $data = json_decode( $response->getBody()->getContents(), true );

            if( !isset( $data['data'] ) ) throw new \Exception( 'Got empty response | Get all OLX districts' );

            return $data['data'];

        } catch ( \Exception $e ){
            throw $e;
        }
    }

    /**
     * @param int $district_id
     * @return array
     * @throws \Exception
     */
    public function get(int $district_id) : array
    {
        try {
            $response = $this->guzzleClient->request('GET', self::OLX_DISTRICTS_URL .'/' .$district_id, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ]
            ]);

            $data = json_decode( $response->getBody()->getContents(), true );

            if( !isset( $data['data'] ) ) throw new \Exception( 'Got empty response | Get all OLX district' );

            return $data['data'];

        } catch ( \Exception $e ){
            throw $e;
        }
    }
}