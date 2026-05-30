<?php

declare(strict_types=1);

namespace Parhomenko\Olx;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Parhomenko\Olx\Api\Adverts;
use Parhomenko\Olx\Api\Authenticator;
use Parhomenko\Olx\Api\Categories;
use Parhomenko\Olx\Api\Cities;
use Parhomenko\Olx\Api\Currencies;
use Parhomenko\Olx\Api\Districts;
use Parhomenko\Olx\Api\Languages;
use Parhomenko\Olx\Api\Locations;
use Parhomenko\Olx\Api\Messages;
use Parhomenko\Olx\Api\Packets;
use Parhomenko\Olx\Api\PaidFeatures;
use Parhomenko\Olx\Api\Regions;
use Parhomenko\Olx\Api\Threads;
use Parhomenko\Olx\Api\Users;
use Parhomenko\Olx\Api\UsersBusiness;

class Api implements OlxApiInterface
{
    private Authenticator $authenticator;
    private Client $guzzleClient;

    private Categories|null $categories = null;
    private Adverts|null $adverts = null;
    private Cities|null $cities = null;
    private Districts|null $districts = null;
    private Regions|null $regions = null;
    private Currencies|null $currencies = null;
    private Users|null $users = null;
    private Languages|null $languages = null;
    private Threads|null $threads = null;
    private Messages|null $messages = null;
    private Packets|null $packets = null;
    private PaidFeatures|null $paidFeatures = null;
    private Locations|null $locations = null;
    private UsersBusiness|null $usersBusiness = null;

    /**
     * @param string $base_uri
     * @param Credentials|array<string, mixed> $credentials credential set; client_id and client_secret are required
     * @param bool $update_token refresh the access token on construction if it has expired
     * @throws Exception|GuzzleException if required credentials are missing or the token refresh fails
     */
    public function __construct(string $base_uri, Credentials|array $credentials, bool $update_token = false)
    {
        $credentials = $credentials instanceof Credentials ? $credentials : Credentials::fromArray($credentials);

        $this->guzzleClient = new Client(['base_uri' => $base_uri]);
        $this->authenticator = new Authenticator($this->guzzleClient, $credentials, $base_uri);

        if ($update_token) {
            $this->authenticator->checkToken();
        }
    }

    public function authenticator(): Authenticator
    {
        return $this->authenticator;
    }

    public function categories(): Categories
    {
        return $this->categories ?? ($this->categories = new Categories($this->authenticator, $this->guzzleClient));
    }

    public function adverts(): Adverts
    {
        return $this->adverts ?? ($this->adverts = new Adverts($this->authenticator, $this->guzzleClient));
    }

    public function regions(): Regions
    {
        return $this->regions ?? ($this->regions = new Regions($this->authenticator, $this->guzzleClient));
    }

    public function cities(): Cities
    {
        return $this->cities ?? ($this->cities = new Cities($this->authenticator, $this->guzzleClient));
    }

    public function districts(): Districts
    {
        return $this->districts ?? ($this->districts = new Districts($this->authenticator, $this->guzzleClient));
    }

    public function currencies(): Currencies
    {
        return $this->currencies ?? ($this->currencies = new Currencies($this->authenticator, $this->guzzleClient));
    }

    public function users(): Users
    {
        return $this->users ?? ($this->users = new Users($this->authenticator, $this->guzzleClient));
    }

    public function languages(): Languages
    {
        return $this->languages ?? ($this->languages = new Languages($this->authenticator, $this->guzzleClient));
    }

    public function threads(): Threads
    {
        return $this->threads ?? ($this->threads = new Threads($this->authenticator, $this->guzzleClient));
    }

    public function messages(): Messages
    {
        return $this->messages ?? ($this->messages = new Messages($this->authenticator, $this->guzzleClient));
    }

    public function packets(): Packets
    {
        return $this->packets ?? ($this->packets = new Packets($this->authenticator, $this->guzzleClient));
    }

    public function paidFeatures(): PaidFeatures
    {
        return $this->paidFeatures ?? ($this->paidFeatures = new PaidFeatures($this->authenticator, $this->guzzleClient));
    }

    public function locations(): Locations
    {
        return $this->locations ?? ($this->locations = new Locations($this->authenticator, $this->guzzleClient));
    }

    public function usersBusiness(): UsersBusiness
    {
        return $this->usersBusiness ?? ($this->usersBusiness = new UsersBusiness($this->authenticator, $this->guzzleClient));
    }
}
