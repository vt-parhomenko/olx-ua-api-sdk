<?php

namespace Parhomenko\Olx\Api;

use GuzzleHttp\Client;

class Languages
{
    const API_VERSION = '2.0';
    const OLX_LANGUAGES_URL = '/api/partner/languages';

    private $user;
    private $guzzleClient;

    /**
     * Languages constructor.
     * @param User $user
     * @param Client $guzzleClient
     */
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
            $response = $this->guzzleClient->request('GET', self::OLX_LANGUAGES_URL, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if( !isset( $data['data'] ) )
                throw new \Exception( 'Got empty response | Get all OLX regions' );

            return $data['data'];

        } catch ( \Exception $e ){
            throw $e;
        }
    }
}