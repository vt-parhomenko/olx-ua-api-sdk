<?php
namespace Parhomenko\Olx;

use Parhomenko\Olx\Api\Adverts;
use Parhomenko\Olx\Api\Categories;
use Parhomenko\Olx\Api\Cities;
use Parhomenko\Olx\Api\Currencies;
use Parhomenko\Olx\Api\Districts;
use Parhomenko\Olx\Api\Languages;
use Parhomenko\Olx\Api\Regions;
use Parhomenko\Olx\Api\User;
use Parhomenko\Olx\Api\Users;

interface IOlxApi
{
    public function user() : User;
    public function categories() : Categories;
    public function adverts() : Adverts;
    public function regions() : Regions;
    public function cities() : Cities;
    public function districts() : Districts;
    public function currencies() : Currencies;
    public function users() : Users;
    public function languages() : Languages;
}