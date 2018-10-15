<?php
namespace Parhomenko\Olx\Ua;

use GuzzleHttp\Client;

class Adverts
{
    const API_VERSION = '2.0';
    const OLX_ADVERTS_URL = '/api/partner/adverts';

    private $user;
    private $guzzleClient;

    public function __construct( User $user, Client $guzzleClient )
    {
        $this->user = $user;
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * Get one advert from OLX.UA by ID
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function get( int $id ) : array
    {

        try{

            $response = $this->guzzleClient->request( 'GET', self::OLX_ADVERTS_URL .'/' .$id, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ]
            ] );

            $advert = json_decode( $response->getBody()->getContents(), true );
            if( !isset( $advert['data'] ) ) throw new \Exception( 'Got empty response | Get OLX.ua advert' );

            return $advert['data'];

        }
        catch ( \Exception $e ){
            throw $e;
        }

    }

    /**
     * Get all adverts from OLX.ua
     * @param int $offset
     * @param int|null $limit
     * @param int|null $external_id
     * @param string $category_ids
     * @return array
     * @throws \Exception
     */
    public function getAll( int $offset = 0, int $limit = null, int $external_id = null, string $category_ids = '' ) : array
    {

        try{

            $params = [];

            $params['offset'] = $offset;
            $params['limit'] = $limit;
            if( $external_id ) $params['external_id'] = $external_id;
            if( !empty($category_ids) ) $params['category_ids'] = $category_ids;

            $response = $this->guzzleClient->request( 'GET', self::OLX_ADVERTS_URL, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ],
                'query' => $params
            ] );

            $adverts = json_decode( $response->getBody()->getContents(), true );

            if( !isset( $adverts['data'] ) ) throw new \Exception( 'Got empty response | Get all OLX.ua adverts' );

            return $adverts['data'];

        }
        catch ( \Exception $e ){
            throw $e;
        }

    }

    /**
     * Create offer in OLX.ua
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function create( array $params ) : array
    {

        try{

            $response = $this->guzzleClient->request( 'POST', self::OLX_ADVERTS_URL, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ],
                'json' => $params
            ] );

            $advert = json_decode( $response->getBody()->getContents(), true );
            if( !isset( $advert['data'] ) ) throw new \Exception( 'Got empty response | Create OLX.ua advert: ' .$params['title'] );

            return $advert['data'];

        }
        catch ( \Exception $e ){
            throw $e;
        }

    }

    /**
     * Update offer in OLX.ua
     * @param int $id
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function update( int $id, array $params ) : array
    {
        try{

            $response = $this->guzzleClient->request( 'PUT', self::OLX_ADVERTS_URL .'/' .$id, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ],
                'json' => $params
            ] );

            $advert = json_decode( $response->getBody()->getContents(), true );
            if( !isset( $advert['data'] ) ) throw new \Exception( 'Got empty response | Update OLX.ua advert: ' .$params['title'] );

            return $advert['data'];

        }
        catch ( \Exception $e ){
            throw $e;
        }
    }

    /**
     * Delete offer from OLX.ua
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function delete( int $id ) : bool
    {
        try{

            $params = ['command' => 'deactivate', 'is_success' => true];

            $this->guzzleClient->request( 'POST', self::OLX_ADVERTS_URL .'/' .$id .'/commands', [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ],
                'json' => $params
            ] );

            $response = $this->guzzleClient->request( 'DELETE', self::OLX_ADVERTS_URL .'/' .$id, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ]
            ] );

            if( $response->getStatusCode() === 204 )
            {
                return true;
            }

            throw new \Exception( $response->getBody()->getContents() );

        }
        catch ( \Exception $e ){
            throw $e;
        }
    }

}