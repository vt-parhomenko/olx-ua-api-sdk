<?php

namespace Parhomenko\Olx\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Parhomenko\Olx\Exceptions\BadRequestException;
use Parhomenko\Olx\Exceptions\ExceptionFactory;

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
     *  Get one advert from OLX by ID
     * @param int $id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Parhomenko\Olx\Exceptions\BadRequestException
     * @throws \Parhomenko\Olx\Exceptions\CallLimitException
     * @throws \Parhomenko\Olx\Exceptions\ForbiddenException
     * @throws \Parhomenko\Olx\Exceptions\NotAcceptableException
     * @throws \Parhomenko\Olx\Exceptions\NotFoundException
     * @throws \Parhomenko\Olx\Exceptions\ServerException
     * @throws \Parhomenko\Olx\Exceptions\UnauthorizedException
     * @throws \Parhomenko\Olx\Exceptions\UnsupportedMediaTypeException
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
            if( !isset( $advert['data'] ) ) throw new BadRequestException( 'Got empty response' );

            return $advert['data'];

        }catch ( ClientException $e )
        {
            ExceptionFactory::throw( $e );
        }

    }

    /**
     * Get all adverts from OLX
     * @param int $offset
     * @param int|null $limit
     * @param int|null $external_id
     * @param string $category_ids
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Parhomenko\Olx\Exceptions\BadRequestException
     * @throws \Parhomenko\Olx\Exceptions\CallLimitException
     * @throws \Parhomenko\Olx\Exceptions\ForbiddenException
     * @throws \Parhomenko\Olx\Exceptions\NotAcceptableException
     * @throws \Parhomenko\Olx\Exceptions\NotFoundException
     * @throws \Parhomenko\Olx\Exceptions\ServerException
     * @throws \Parhomenko\Olx\Exceptions\UnauthorizedException
     * @throws \Parhomenko\Olx\Exceptions\UnsupportedMediaTypeException
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

            if( !isset( $adverts['data'] ) ) throw new BadRequestException( 'Got empty response' );

            return $adverts['data'];

        }catch ( ClientException $e )
        {
            ExceptionFactory::throw( $e );
        }

    }

    /**
     * Create offer in OLX
     * @param array $params
     * @return array
     * @throws BadRequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Parhomenko\Olx\Exceptions\CallLimitException
     * @throws \Parhomenko\Olx\Exceptions\ForbiddenException
     * @throws \Parhomenko\Olx\Exceptions\NotAcceptableException
     * @throws \Parhomenko\Olx\Exceptions\NotFoundException
     * @throws \Parhomenko\Olx\Exceptions\ServerException
     * @throws \Parhomenko\Olx\Exceptions\UnauthorizedException
     * @throws \Parhomenko\Olx\Exceptions\UnsupportedMediaTypeException
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
            if( !isset( $advert['data'] ) ) throw new BadRequestException( 'Got empty response' );

            return $advert['data'];

        }catch ( ClientException $e )
        {
            ExceptionFactory::throw( $e );
        }

    }

    /**
     * Update offer in OLX
     * @param int $id
     * @param array $params
     * @return array
     * @throws BadRequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Parhomenko\Olx\Exceptions\CallLimitException
     * @throws \Parhomenko\Olx\Exceptions\ForbiddenException
     * @throws \Parhomenko\Olx\Exceptions\NotAcceptableException
     * @throws \Parhomenko\Olx\Exceptions\NotFoundException
     * @throws \Parhomenko\Olx\Exceptions\ServerException
     * @throws \Parhomenko\Olx\Exceptions\UnauthorizedException
     * @throws \Parhomenko\Olx\Exceptions\UnsupportedMediaTypeException
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
            if( !isset( $advert['data'] ) ) throw new BadRequestException( 'Got empty response' );

            return $advert['data'];

        }catch ( ClientException $e )
        {
            ExceptionFactory::throw( $e );
        }
    }

    /**
     * Activate offer
     * @param int $id
     * @return bool
     * @throws BadRequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Parhomenko\Olx\Exceptions\CallLimitException
     * @throws \Parhomenko\Olx\Exceptions\ForbiddenException
     * @throws \Parhomenko\Olx\Exceptions\NotAcceptableException
     * @throws \Parhomenko\Olx\Exceptions\NotFoundException
     * @throws \Parhomenko\Olx\Exceptions\ServerException
     * @throws \Parhomenko\Olx\Exceptions\UnauthorizedException
     * @throws \Parhomenko\Olx\Exceptions\UnsupportedMediaTypeException
     */
    public function activate( int $id ) : bool
    {
        try{

            $params = ['command' => 'activate'];

            $response = $this->guzzleClient->request( 'POST', self::OLX_ADVERTS_URL .'/' .$id .'/commands', [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ],
                'json' => $params
            ] );

            if( $response->getStatusCode() === 204 )
            {
                return true;
            }

            throw new BadRequestException( $response->getBody()->getContents() );

        }catch ( ClientException $e )
        {
            ExceptionFactory::throw( $e );
        }
    }

    /**
     * @param int $id
     * @param bool $is_success
     * @return bool
     * @throws BadRequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Parhomenko\Olx\Exceptions\CallLimitException
     * @throws \Parhomenko\Olx\Exceptions\ForbiddenException
     * @throws \Parhomenko\Olx\Exceptions\NotAcceptableException
     * @throws \Parhomenko\Olx\Exceptions\NotFoundException
     * @throws \Parhomenko\Olx\Exceptions\ServerException
     * @throws \Parhomenko\Olx\Exceptions\UnauthorizedException
     * @throws \Parhomenko\Olx\Exceptions\UnsupportedMediaTypeException
     * @throws \Parhomenko\Olx\Exceptions\ValidationException
     */
    public function deactivate( int $id, bool $is_success = true ) : bool
    {
        try{

            $params = ['command' => 'deactivate', 'is_success' => $is_success ];

            $response = $this->guzzleClient->request( 'POST', self::OLX_ADVERTS_URL .'/' .$id .'/commands', [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ],
                'json' => $params
            ] );

            if( $response->getStatusCode() === 204 )
            {
                return true;
            }

            throw new BadRequestException( $response->getBody()->getContents() );

        }catch ( ClientException $e )
        {
            ExceptionFactory::throw( $e );
        }
    }

    /**
     * Delete inactive offer
     * @param int $id
     * @return bool
     * @throws BadRequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Parhomenko\Olx\Exceptions\CallLimitException
     * @throws \Parhomenko\Olx\Exceptions\ForbiddenException
     * @throws \Parhomenko\Olx\Exceptions\NotAcceptableException
     * @throws \Parhomenko\Olx\Exceptions\NotFoundException
     * @throws \Parhomenko\Olx\Exceptions\ServerException
     * @throws \Parhomenko\Olx\Exceptions\UnauthorizedException
     * @throws \Parhomenko\Olx\Exceptions\UnsupportedMediaTypeException
     */
    public function delete_notactive( int $id ) : bool
    {
        try{

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

            throw new BadRequestException( $response->getBody()->getContents() );

        }catch ( ClientException $e )
        {
            ExceptionFactory::throw( $e );
        }
    }

    /**
     * Delete offer
     * @param int $id
     * @return bool
     * @throws BadRequestException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Parhomenko\Olx\Exceptions\CallLimitException
     * @throws \Parhomenko\Olx\Exceptions\ForbiddenException
     * @throws \Parhomenko\Olx\Exceptions\NotAcceptableException
     * @throws \Parhomenko\Olx\Exceptions\NotFoundException
     * @throws \Parhomenko\Olx\Exceptions\ServerException
     * @throws \Parhomenko\Olx\Exceptions\UnauthorizedException
     * @throws \Parhomenko\Olx\Exceptions\UnsupportedMediaTypeException
     */
    public function delete( int $id ) : bool
    {
        try{

            $this->deactivate( $id );

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

            throw new BadRequestException( $response->getBody()->getContents() );

        }catch ( ClientException $e )
        {
            ExceptionFactory::throw( $e );
        }
    }

}