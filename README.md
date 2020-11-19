#Olx REST API PHP SDK
## Installation via composer
```
composer require vt-parhomenko/olx-ua-api-sdk
```
## API Documentation
https://developer.olx.ua/api/doc

## How to use
```php 
<?php

use Parhomenko\Olx\OlxFactory;

$olx = OlxFactory::get( "ua", [
    "client_id"=> 'client_id',
    "client_secret"=> 'client_secret',
    'access_token' => 'access_token',
    'refresh_token' => 'refresh_token',
    'token_type' => 'token_type',
    'scope' => 'scope'
]);

$advert_data = [
//advert data
];

// create new advert
$olx->adverts()->create( $advert_data );

```