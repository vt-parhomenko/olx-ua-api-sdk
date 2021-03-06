<?php

namespace Parhomenko\Olx;

use GuzzleHttp\Client;
use Parhomenko\Olx\Api\Districts;
use Parhomenko\Olx\Api\Languages;
use Parhomenko\Olx\Api\User;
use Parhomenko\Olx\Api\Categories;
use Parhomenko\Olx\Api\Adverts;
use Parhomenko\Olx\Api\Regions;
use Parhomenko\Olx\Api\Cities;
use Parhomenko\Olx\Api\Currencies;
use Parhomenko\Olx\Api\Users;

class Api implements IOlxApi
{
    private $user;
    private $guzzleClient;

    private $categories = null;
    private $adverts = null;
    private $cities = null;
    private $districts = null;
    private $regions = null;
    private $currencies = null;
    private $users = null;
    private $languages = null;

    /**
     * Api constructor.
     * @param string $base_uri
     * @param array $credentials
     * @param bool $update_token
     */
    public function __construct( string $base_uri, array $credentials, bool $update_token = false )
    {
        $this->guzzleClient = new Client(['base_uri' => $base_uri]);
        $this->user = new User( $this->guzzleClient, $credentials );
        if ($update_token) $this->user->checkToken();
    }

    /**
     * @return User
     */
    public function user() : User{
        return $this->user;
    }

    /**
     * @return Categories
     */
    public function categories() : Categories{
        return is_null($this->categories) ? new Categories( $this->user, $this->guzzleClient ) : $this->categories;
    }

    /**
     * @return Adverts
     */
    public function adverts() : Adverts
    {
        return is_null($this->adverts ) ? new Adverts( $this->user, $this->guzzleClient ) : $this->adverts;
    }

    /**
     * @return Regions
     */
    public function regions() : Regions
    {
        return is_null($this->regions ) ? new Regions( $this->user, $this->guzzleClient ) : $this->regions;
    }

    /**
     * @return Cities
     */
    public function cities() : Cities
    {
        return is_null($this->cities ) ? new Cities( $this->user, $this->guzzleClient ) : $this->cities;
    }

    /**
     * @return Districts
     */
    public function districts() : Districts
    {
        return is_null($this->districts ) ? new Districts( $this->user, $this->guzzleClient ) : $this->districts;
    }

    /**
     * @return Currencies
     */
    public function currencies() : Currencies
    {
        return is_null($this->currencies ) ? new Currencies( $this->user, $this->guzzleClient ) : $this->currencies;
    }

    /**
     * @return Users
     */
    public function users() : Users{
        return is_null($this->users) ? new Users( $this->user, $this->guzzleClient ) : $this->users;
    }

    /**
     * @return Languages
     */
    public function languages() : Languages{
        return is_null($this->languages) ? new Languages( $this->user, $this->guzzleClient ) : $this->languages;
    }

}