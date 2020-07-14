<?php

namespace Parhomenko\Olx\Api;

use GuzzleHttp\Client;

class Categories
{
    const API_VERSION = '2.0';
    const OLX_CATEGORIES_URL = '/api/partner/categories';

    private $user;
    private $guzzleClient;

    public function __construct( User $user, Client $guzzleClient )
    {
        $this->user = $user;
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * Get all olx categories or one specified
     * @param int $category_id
     * @return array|mixed
     * @throws \Exception
     */
    public function get( int $category_id ) : array
    {

        try{

            $response = $this->guzzleClient->request( 'GET', self::OLX_CATEGORIES_URL . '/' .$category_id , [ 'headers' => [
                'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                'Version' => self::API_VERSION
            ]] );

            $categories = json_decode( $response->getBody()->getContents(), true );

            if( !isset( $categories['data'] ) ) throw new \Exception( 'Got empty response | Get OLX.ua category: ' .$category_id );

            return $categories['data'];

        }catch ( \Exception $e ){
            throw $e;
        }

    }

    public function getAll( int $parent_id = 0 ) : array
    {

        try{

            $query = $parent_id ? ['parent_id' => $parent_id] : [];

            $response = $this->guzzleClient->request( 'GET', self::OLX_CATEGORIES_URL, [ 'headers' => [
                'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                'Version' => self::API_VERSION
            ],
                'query' => $query
            ] );

            $categories = json_decode( $response->getBody()->getContents(), true );

            if( !isset( $categories['data'] ) ) throw new \Exception( 'Got empty response | Get all OLX.ua categories, parent_id: ' .$parent_id );

            return $categories['data'];

        }catch ( \Exception $e ){
            throw $e;
        }

    }

    /**
     * Get olx category attributes
     * @param int $category_id
     * @return array
     * @throws \Exception
     */
    public function attributes( int $category_id ) : array
    {
        try{

            $response = $this->guzzleClient->request( 'GET', self::OLX_CATEGORIES_URL .'/' . $category_id .'/attributes' , [ 'headers' => [
                'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                'Version' => self::API_VERSION
            ]] );

            $attributes = json_decode( $response->getBody()->getContents(), true );

            if( !isset( $attributes['data'] ) ) throw new \Exception( 'Got empty response | Get OLX.ua category attributes: ' .$category_id );

            return $attributes['data'];

        }catch ( \Exception $e ){
            throw $e;
        }
    }

}