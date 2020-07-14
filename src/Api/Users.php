<?php

namespace Parhomenko\Olx\Api;

use GuzzleHttp\Client;

class Users
{
    const API_VERSION = '2.0';
    const OLX_AUTHENTICATED_USER_URL = '/api/partner/users/me';
    const OLX_USER_URL = '/api/partner/users';

    private $user;
    private $guzzleClient;
    
    public function __construct( User $user, Client $guzzleClient )
    {
        $this->user = $user;
        $this->guzzleClient = $guzzleClient;
    }
    
    
    /**
     * Return information about current user
     * @return array
     * @throws \Exception
     */
    public function me() : array
    {
        $me_info = array();
        try{

            $response = $this->guzzleClient->request( 'GET', self::OLX_AUTHENTICATED_USER_URL, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ]
            ] );

            $me_info = json_decode( $response->getBody()->getContents(), true );

            if( !isset( $me_info['data'] ) ) throw new \Exception( 'Got empty response | Get authenticated user' );

            //return $me_info['data'];
            $me_info = $me_info['data'];

        }
        catch ( \Exception $e ){
            //print_r($e);
            throw $e;
        }
        
        return $me_info;
    }
    
    /**
     * Get one user from OLX.UA by ID
     * @param int $user_id
     * @return array
     * @throws \Exception
     */
    public function get( int $user_id ) : array
    {

        try{

            $response = $this->guzzleClient->request( 'GET', self::OLX_USER_URL .'/' .$user_id, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ]
            ] );

            $user_info = json_decode( $response->getBody()->getContents(), true );
            
            if( !isset( $user_info['data'] ) ) throw new \Exception( 'Got empty response | Get OLX.ua advert' );

            return $user_info['data'];

        }
        catch ( \Exception $e ){
            throw $e;
        }

    }
}