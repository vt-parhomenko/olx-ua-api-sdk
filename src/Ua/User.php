<?php
namespace Parhomenko\Olx\Ua;


use GuzzleHttp\Client;

class User
{
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

    private $required_auth_credentials = [
        'access_token',
        'refresh_token',
        'expires_in',
        'updated_at'
    ];

    public function __construct( Client $guzzleClient, $credentials )
    {
        $this->valideteCredentials( $credentials );

        $this->guzzleClient = $guzzleClient;
        $this->client_id = $credentials['client_id'];
        $this->client_secret = $credentials['client_secret'];

        $this->access_token = $credentials['access_token'];
        $this->refresh_token = $credentials['refresh_token'];
        $this->token_type = $credentials['token_type'] ?? self::OLX_AUTH_DEFAULT_TOKEN_TYPE;
        $this->grant_type = $credentials['grant_type'] ?? self::OLX_AUTH_DEFAULT_GRAND_TYPE;
        $this->scope = $credentials['scope'] ?? self::OLX_AUTH_DEFAULT_SCOPE;
        $this->token_expires_in = $credentials['expires_in'];
        $this->token_updated_at = $credentials['updated_at'];
    }

    /**
     * @param array $credintials
     * @return bool
     * @throws \Exception
     */
    private function valideteCredentials( array $credintials )
    {
        $missing_credentials = [];

        foreach ($this->required_credentials as $required_credential )
        {
            if( !key_exists( $required_credential, $credintials ) ) $missing_credentials[] = $required_credential;
        }

        if( !empty($missing_credentials) ) throw new \Exception('Missing credentials: ' .implode(', ',$missing_credentials ) );

        return true;
    }

    public function getTokenType(){
        return $this->token_type;
    }

    public function getAccessToken(){
        return $this->access_token;
    }

    public function checkToken()
    {

        if( !$this->access_token )
        {
            $this->refreshToken();
        }else{

            $date_time_expiries = new \DateTime($this->token_updated_at);
            $date_time_expiries->add( new \DateInterval( 'PT' .$this->token_expires_in .'S' ) );

            if( $date_time_expiries <= new \DateTime() )
            {
                $this->refreshToken();
            }

        }

    }


    /**
     * Log in to olx
     */
    public function login()
    {

        try {

            $response = $this->guzzleClient->request('POST', '/api/open/oauth/token', [ 'json' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => $this->grant_type,
                'scope' => $this->scope
                ]
            ]);

            $data = json_decode( $response->getBody()->getContents(), true );

            if( !empty($data['access_token']) ){
                $this->token = $data['access_token'];
                $this->token_type = $data['token_type'];
                $this->refresh_token = $data['refresh_token'];
                $this->token_expires_in = $data['expires_in'];
            }else{
                throw new \Exception( 'Can not get access token' );
            }

        } catch (\Exception $e) {
            throw $e;
        }

        return $this;
    }

    public function authorize(){

        try {

            $response = $this->guzzleClient->request('POST', '/api/open/oauth/token', [ 'json' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => self::OLX_AUTH_DEFAULT_GRAND_TYPE,
                'scope' => $this->scope,
                //'redirect_uri' => 'http://cb69336f.ngrok.io/oauth/olx',
                'code' => '4fb41f60d5d3efe73673a44c5ae5bcca884b42d5'
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
                throw new \Exception( 'Can not get access token' );
            }

        } catch (\Exception $e) {
            throw $e;
        }

        return $this;

    }

    public function refreshToken(){

    }

}