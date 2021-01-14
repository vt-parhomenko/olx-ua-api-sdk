<?php

namespace Parhomenko\Olx\Api;

use GuzzleHttp\Client;

class Threads
{
    const API_VERSION = '2.0';
    const OLX_THREADS_URL = '/api/partner/threads';

    private $user;
    private $guzzleClient;
    
    public function __construct( User $user, Client $guzzleClient )
    {
        $this->user = $user;
        $this->guzzleClient = $guzzleClient;
    }
    
    /**
     * Get OLX.ua thread info 
     * @param int $thread_id
     * @return array
     * @throws \Exception
     */
    public function get( int $thread_id ) : array
    {

        try{

            $response = $this->guzzleClient->request( 'GET', self::OLX_THREADS_URL.'/'.$thread_id, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ]
            ] );

            $thread = json_decode( $response->getBody()->getContents(), true );

            if( !isset( $thread['data'] ) ) throw new \Exception( 'Got empty response | Get OLX thread' );

            return $thread['data'];

        }
        catch ( \Exception $e ){
            throw $e;
        }

    }
    
    /**
     * Get all threads from OLX.ua
     * @param int $offset
     * @param int|null $limit
     * @param int|null $advert_id
     * @param int|null $interlocutor_id
     * @return array
     * @throws \Exception
     */
    public function getAll( int $offset = 0, int $limit = null, int $advert_id = null, int $interlocutor_id = null ) : array
    {

        try{

            $params = [];

            $params['offset'] = $offset;
            $params['limit'] = $limit;
            if( $advert_id ) $params['advert_id'] = $advert_id;
            if( $interlocutor_id ) $params['interlocutor_id'] = $interlocutor_id;

            $response = $this->guzzleClient->request( 'GET', self::OLX_THREADS_URL, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ],
                'query' => $params
            ] );

            $threads = json_decode( $response->getBody()->getContents(), true );

            if( !isset( $threads['data'] ) ) throw new \Exception( 'Got empty response | Get all OLX threads' );

            return $threads['data'];

        }
        catch ( \Exception $e ){
            throw $e;
        }

    }
    
    /**
     * Mark thread as readed
     * @param int $thread_id
     * @return bool
     * @throws \Exception
     */
    public function mark_as_read( int $thread_id ) : bool
    {
        try{

            $params = ['command' => 'mark-as-read'];
            
            $apiurl = self::OLX_THREADS_URL.'/'.$thread_id.'/commands';

            $response = $this->guzzleClient->request( 'POST', $apiurl, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ],
                'json' => $params
            ] );
            
            //var_dump($response->getBody()->getContents());

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
    
    /**
     * Mark thread as favourite
     * @param int $thread_id
     * @return bool
     * @throws \Exception
     */
    public function set_favourite( int $thread_id ) : bool
    {
        try{

            $params = ['command' => 'set-favourite', 'set-favourite' => true];
            
            $apiurl = self::OLX_THREADS_URL.'/'.$thread_id.'/commands';

            $response = $this->guzzleClient->request( 'POST', $apiurl, [
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

            throw new \Exception( $response->getBody()->getContents() );

        }
        catch ( \Exception $e ){
            throw $e;
        }
    }
    
    /**
     * Unset favourite mark from thread
     * @param int $thread_id
     * @return bool
     * @throws \Exception
     */
    public function unset_favourite( int $thread_id ) : bool
    {
        try{

            $params = ['command' => 'set-favourite', 'set-favourite' => false];
            
            $apiurl = self::OLX_THREADS_URL.'/'.$thread_id.'/commands';

            $response = $this->guzzleClient->request( 'POST', $apiurl, [
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

            throw new \Exception( $response->getBody()->getContents() );

        }
        catch ( \Exception $e ){
            throw $e;
        }
    }
    
    /**
     * Post message to the thread
     * @param int $thread_id
     * @param string $text
     * @return bool
     * @throws \Exception
     */
    public function post( int $thread_id, string $text ) : bool
    {

        try{
            
            $params = ['text' => $text];
            
            $apiurl = self::OLX_THREADS_URL.'/'.$thread_id.'/messages';

            $response = $this->guzzleClient->request( 'POST', $apiurl, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ],
                'json' => $params
            ] );
            //var_dump($response->getStatusCode());
            if( $response->getStatusCode() === 200 )
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