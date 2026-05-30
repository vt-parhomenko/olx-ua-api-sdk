<?php

declare(strict_types=1);

namespace Parhomenko\Olx;

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

interface OlxApiInterface
{
    public function authenticator(): Authenticator;
    public function categories(): Categories;
    public function adverts(): Adverts;
    public function regions(): Regions;
    public function cities(): Cities;
    public function districts(): Districts;
    public function currencies(): Currencies;
    public function users(): Users;
    public function languages(): Languages;
    public function threads(): Threads;
    public function messages(): Messages;
    public function packets(): Packets;
    public function paidFeatures(): PaidFeatures;
    public function locations(): Locations;
    public function usersBusiness(): UsersBusiness;
}
