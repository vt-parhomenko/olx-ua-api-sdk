<?php
namespace Parhomenko\Olx\Ua;

use GuzzleHttp\Client;

class Messages
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
     * Get all messages for thread from OLX.ua
     * @param int $thread_id
     * @param int $offset
     * @param int|null $limit
     * @return array
     * @throws \Exception
     */
    public function get( int $thread_id, int $offset = 0, int $limit = null ) : array
    {

        try{
            
            $apiurl = '/api/partner/threads/'.$thread_id.'/messages';
            
            $params = [];

            $params['offset'] = $offset;
            $params['limit'] = $limit;
            
            $response = $this->guzzleClient->request( 'GET', $apiurl, [
                'headers' => [
                    'Authorization' => $this->user->getTokenType() .' ' .$this->user->getAccessToken(),
                    'Version' => self::API_VERSION
                ]
            ] );

            $messages = json_decode( $response->getBody()->getContents(), true );

            if( !isset( $messages['data'] ) ) throw new \Exception( 'Got empty response | Get all OLX.ua thread messages' );

            return $messages['data'];

        }
        catch ( \Exception $e ){
            //print_r($e);
            throw $e;
        }

    }
    
    
    
    
}