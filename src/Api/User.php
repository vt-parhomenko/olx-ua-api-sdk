<?php

namespace Parhomenko\Olx\Api;

use GuzzleHttp\Client;
use function GuzzleHttp\Psr7\build_query;

class User
{
    const OLX_AUTH_REQUEST_URI = '/api/open/oauth/token';
    const OLX_AUTH_DEFAULT_GRAND_TYPE = 'authorization_code';
    const OLX_AUTH_DEFAULT_SCOPE = 'read write v2';
    const OLX_AUTH_DEFAULT_TOKEN_TYPE = 'bearer';

    private $guzzleClient;
    private $client_id;
    private $client_secret;
    private $access_token;
    private $refresh_token;
    private $token_type;
    private $token_expires_in;
    private $token_updated_at;
    private $grant_type;
    private $scope;

    private $required_credentials = [
        'client_id',
        'client_secret'
    ];

    public function __construct( Client $guzzleClient, $credentials )
    {
        $this->validateCredentials( $credentials );

        $this->guzzleClient = $guzzleClient;

        $this->client_id = $credentials['client_id'];
        $this->client_secret = $credentials['client_secret'];
        $this->access_token = $credentials['access_token'] ?? null;
        $this->refresh_token = $credentials['refresh_token'] ?? null;
        $this->token_type = $credentials['token_type'] ?? self::OLX_AUTH_DEFAULT_TOKEN_TYPE;
        $this->grant_type = $credentials['grant_type'] ?? self::OLX_AUTH_DEFAULT_GRAND_TYPE;
        $this->scope = $credentials['scope'] ?? self::OLX_AUTH_DEFAULT_SCOPE;
        $this->token_expires_in = $credentials['expires_in'] ?? 0;
        $this->token_updated_at = $credentials['updated_at'] ?? '2000-01-01 00:00:00';
    }

    /**
     * @return integer
     */
    public function getClientId(){
        return $this->client_id;
    }

    /**
     * @return string
     */
    public function getClientSecret(){
        return $this->client_secret;
    }

    /**
     * @return string
     */
    public function getTokenType(){
        return $this->token_type;
    }

    /**
     * @return string
     */
    public function getAccessToken(){
        return $this->access_token;
    }

    /**
     * @return string
     */
    public function getRefreshToken(){
        return $this->refresh_token;
    }

    /**
     * @return string
     */
    public function getTokenExpiresIn(){
        return $this->token_expires_in;
    }

    /**
     * @param array $credentials
     * @return bool
     * @throws \Exception
     */
    private function validateCredentials( array $credentials )
    {
        $missing_credentials = [];

        foreach ($this->required_credentials as $required_credential )
        {
            if( !key_exists( $required_credential, $credentials ) ) $missing_credentials[] = $required_credential;
        }

        if( !empty($missing_credentials) ) throw new \Exception('Missing credentials: ' .implode(', ',$missing_credentials ) );

        return true;
    }

    /**
     * Check if token is invalid or unexpected
     */
    public function checkToken() : self
    {

        if( !$this->access_token )
        {
            $this->refreshToken();
        }else{

            $date_time_expires = new \DateTime( $this->token_updated_at );
            $date_time_expires->add( new \DateInterval( 'PT' .$this->token_expires_in .'S' ) );

            if( $date_time_expires <= new \DateTime() )
            {
                $this->refreshToken();
            }

        }

        return $this;
    }

    /**
     * Step 1. Get OAuth link
     * @param string $redirect_uri
     * @param string $state
     * @return string
     */
    public function getOAuthLink( string $redirect_uri = null, string $state = null ) : string
    {
        $params = [
            'client_id' =>$this->client_id,
            'response_type' => 'code',
            'scope' => self::OLX_AUTH_DEFAULT_SCOPE,
        ];

        if( !is_null($redirect_uri) ) $params['redirect_uri'] = $redirect_uri;
        if( !is_null($state) ) $params['state'] = $state;

        return $this->guzzleClient->getConfig( 'base_uri') .'oauth/authorize/?' .build_query( $params );
    }

    /**
     * Step2. Get access token via code
     * @param string $code
     * @param null $redirect_uri
     * @return User
     * @throws \Exception
     */
    public function authorize( string $code, $redirect_uri = null ) : self
    {
        try{

            $request_data = [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => self::OLX_AUTH_DEFAULT_GRAND_TYPE,
                'scope' => $this->scope,
                'code' => $code
            ];

            if( !is_null($redirect_uri) ) $request_data['redirect_uri'] = $redirect_uri;

            $response = $this->guzzleClient->request('POST', self::OLX_AUTH_REQUEST_URI, [ 'json' => $request_data ] );

            $data = json_decode( $response->getBody()->getContents(), true );

            if( !empty($data['access_token']) ){
                $this->access_token = $data['access_token'];
                $this->token_type = $data['token_type'];
                $this->refresh_token = $data['refresh_token'];
                $this->token_expires_in = $data['expires_in'];
                $this->token_updated_at = date( "Y-m-d H:i:s");
            }else{
                throw new \Exception( 'Can not get access token' );
            }
        }
        catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    /**
     * Refresh access token via refresh_token
     * @return User
     * @throws \Exception
     */
    public function refreshToken() : self
    {
        try {

            $response = $this->guzzleClient->request('POST', self::OLX_AUTH_REQUEST_URI, [ 'json' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => "refresh_token",
                'refresh_token' => $this->refresh_token
            ]
            ]);

            $data = json_decode( $response->getBody()->getContents(), true );

            if( !empty($data['access_token']) ){
                $this->access_token = $data['access_token'];
                $this->token_type = $data['token_type'];
                $this->refresh_token = $data['refresh_token'];
                $this->token_expires_in = $data['expires_in'];
                $this->token_updated_at = date( "Y-m-d H:i:s");
            }else{
                throw new \Exception( 'Can not refresh access token' );
            }

        } catch ( \Exception $e ) {
            throw $e;
        }

        return $this;
    }

}