<?php
namespace Parhomenko\Olx\Ua;
use GuzzleHttp\Client;
use Parhomenko\Olx\IOlxApi;

class Api implements IOlxApi
{
    const BASE_URI = 'https://www.olx.ua/';

    private $user;
    private $guzzleClient;

    private $categories = null;
    private $adverts = null;

    public function __construct( array $credentials, bool $update_token = false )
    {
        $this->guzzleClient = new Client(['base_uri' => self::BASE_URI]);
        $this->user = new User( $this->guzzleClient, $credentials );
        if($update_token) $this->user->checkToken();
    }

    /**
     * @return User
     */
    public function user(){
        return $this->user;
    }

    /**
     * @return Categories
     */
    public function categories(){
        return is_null($this->categories) ? new Categories( $this->user, $this->guzzleClient ) : $this->categories;
    }

    /**
     * @return Adverts
     */
    public function adverts()
    {
        return is_null($this->adverts ) ? new Adverts( $this->user, $this->guzzleClient ) : $this->adverts;
    }

}