# OLX Partner API — PHP SDK

A lightweight PHP client for the [OLX Partner REST API](https://developer.olx.ua/api/doc).
It wraps authentication (OAuth2), request signing, response parsing and error
handling behind a small, fluent facade.

Supports OLX in Ukraine, Poland, Bulgaria, Romania, Kazakhstan and Portugal.

## Requirements

- PHP **8.1+**
- `guzzlehttp/guzzle` `^7.5`

## Installation

```bash
composer require vt-parhomenko/olx-ua-api-sdk
```

## Quick start

```php
<?php

use Parhomenko\Olx\OlxFactory;
use Parhomenko\Olx\Country;

$olx = OlxFactory::get(Country::UA, [
    'client_id'     => 'your-client-id',
    'client_secret' => 'your-client-secret',
    'access_token'  => 'stored-access-token',
    'refresh_token' => 'stored-refresh-token',
    'token_type'    => 'Bearer',
    'expires_in'    => 86400,
    'updated_at'    => '2026-05-30T12:00:00+03:00', // ISO-8601 (offset-aware); when issued/stored
], $update_token = true); // auto-refresh the access token if it has expired

// Create an advert
$advert = $olx->adverts()->create([
    // ...advert payload, see the OLX API docs
]);
```

`OlxFactory::get()` accepts either a `Country` enum case (recommended) or a plain
country code string (`'ua'`, `'pl'`, `'bg'`, `'ro'`, `'kz'`, `'pt'`). An unknown
country throws `Parhomenko\Olx\Exceptions\UnknownCountryException`.

The third argument, `$update_token`, when `true`, checks the token on
construction and transparently refreshes it via the refresh token if it is
expired.

## HTTP client options

The optional fourth argument, `$httpOptions` (since 3.1), is passed to the
underlying [Guzzle client](https://docs.guzzlephp.org/en/stable/request-options.html)
and applies to every request — resource calls as well as the OAuth token
requests. By default no options are set, and Guzzle then waits **indefinitely**
for a response, so setting at least `timeout` and `connect_timeout` is strongly
recommended for queue workers and other long-running processes.

```php
use GuzzleHttp\HandlerStack;
use Parhomenko\Olx\Country;
use Parhomenko\Olx\OlxFactory;

$stack = HandlerStack::create();
$stack->push($myLoggingOrRetryMiddleware); // any Guzzle middleware

$olx = OlxFactory::get(Country::UA, $credentials, false, [
    'timeout'         => 30,   // total seconds per request
    'connect_timeout' => 5,    // seconds to establish the connection
    'handler'         => $stack,
]);

$olx->getHttpClient(); // the configured GuzzleHttp\ClientInterface
```

The same array can be passed to `new Api($baseUri, $credentials, $update_token, $httpOptions)`.
A `base_uri` key in the options is ignored: the base URI of the selected
country (or the `$baseUri` argument of `Api`) always wins. A timeout surfaces
as `GuzzleHttp\Exception\ConnectException` (a `GuzzleException`), not as an
OLX exception.

## Authentication (OAuth2)

Only `client_id` and `client_secret` are strictly required. The full credential
set is:

| Key             | Required | Description                                             |
|-----------------|----------|---------------------------------------------------------|
| `client_id`     | yes      | OAuth client id                                         |
| `client_secret` | yes      | OAuth client secret                                     |
| `access_token`  | no       | Stored access token                                     |
| `refresh_token` | no       | Stored refresh token                                    |
| `token_type`    | no       | Defaults to `bearer`                                    |
| `scope`         | no       | Defaults to `read write v2`                             |
| `grant_type`    | no       | Defaults to `authorization_code`                        |
| `expires_in`    | no       | Token lifetime in seconds (used to detect expiry)       |
| `updated_at`    | no       | ISO-8601, e.g. `date('c')`, when issued/stored          |

> **Note:** store `expires_in` whenever you store an `access_token`. If you
> supply an `access_token` and `updated_at` but omit `expires_in`, the token is
> treated as already expired and refreshed on the first call (which needs a
> valid `refresh_token`). Prefer persisting the value returned by
> `Authenticator::getTokenUpdatedAt()` for `updated_at` — it is offset-aware, so
> expiry is evaluated correctly even when tokens are stored and read on hosts
> with different timezones.

### Authorization code flow

```php
use Parhomenko\Olx\OlxFactory;
use Parhomenko\Olx\Country;

$olx = OlxFactory::get(Country::UA, [
    'client_id'     => 'your-client-id',
    'client_secret' => 'your-client-secret',
]);

// 1. Redirect the user to the OLX consent screen
$url = $olx->authenticator()->getOAuthLink('https://your-app.test/callback', 'optional-state');
header('Location: ' . $url);

// 2. On your callback, exchange the received ?code for tokens
$auth = $olx->authenticator()->authorize($_GET['code'], 'https://your-app.test/callback');

// 3. Persist the tokens for later use
$tokens = [
    'access_token'  => $auth->getAccessToken(),
    'refresh_token' => $auth->getRefreshToken(),
    'token_type'    => $auth->getTokenType(),
    'expires_in'    => $auth->getTokenExpiresIn(),
    'updated_at'    => $auth->getTokenUpdatedAt(), // ISO-8601, set by the SDK on authorize/refresh
];
```

### Refreshing tokens

```php
// Refresh only when needed (compares updated_at + expires_in to now)
$olx->authenticator()->checkToken();

// Or force a refresh
$olx->authenticator()->refreshToken();
```

A failed refresh throws `Parhomenko\Olx\Exceptions\RefreshTokenException`
(use `getError()` / `getErrorDescription()` for OLX error details).

### Client credentials (no user context)

For read-only access to public catalog data (categories, cities, currencies, …)
without a user, authenticate with the `client_credentials` grant:

```php
$olx->authenticator()->authenticateAsClient(); // grant_type=client_credentials
$categories = $olx->categories()->getAll();
```

Note: this grant cannot create or manage adverts — use the authorization code
flow for user-scoped actions.

## Resources

Every resource is reached through the facade, e.g. `$olx->adverts()`.
List endpoints return the decoded `data` payload as an array; command endpoints
return `void` and throw a `Parhomenko\Olx\Exceptions\BaseOlxException` on failure.

### Adverts — `$olx->adverts()`

```php
$olx->adverts()->get(int $id): array;
$olx->adverts()->getAll(int $offset = 0, ?int $limit = null, ?string $external_id = null, string $category_ids = ''): array;
$olx->adverts()->create(array $params): array;
$olx->adverts()->update(int $id, array $params): array;
$olx->adverts()->activate(int $id): void;
$olx->adverts()->deactivate(int $id, bool $is_success = true): void;
$olx->adverts()->delete(int $id): void; // single DELETE request
$olx->adverts()->statistics(int $id): array;
$olx->adverts()->deleteStatistic(int $id, string $statistic_name): void;
$olx->adverts()->moderationReason(int $id): array; // why a rejected advert was refused
$olx->adverts()->logos(int $id): array;
$olx->adverts()->addLogo(int $id, array $params): array;
$olx->adverts()->deleteLogo(int $id, int $logo_id): void;
```

### Categories — `$olx->categories()`

```php
$olx->categories()->get(int $category_id): array;
$olx->categories()->getAll(int $parent_id = 0): array;
$olx->categories()->attributes(int $category_id): array;
$olx->categories()->suggestion(string $q): array; // suggest categories by advert title (min. 3 chars)
```

### Locations

```php
$olx->cities()->getAll(int $offset = 0, ?int $limit = null): array;
$olx->cities()->get(int $city_id): array;
$olx->cities()->districts(int $city_id): array; // districts of a city

$olx->regions()->getAll(): array;
$olx->regions()->get(int $region_id): array;

$olx->districts()->getAll(): array;
$olx->districts()->get(int $district_id): array;

$olx->locations()->getByCoordinates(float $latitude, float $longitude): array;
```

### Reference data

```php
$olx->currencies()->getAll(int $offset = 0, ?int $limit = null): array;
$olx->languages()->getAll(): array;
```

### Users — `$olx->users()`

```php
$olx->users()->me(): array;            // the authenticated user
$olx->users()->get(int $user_id): array;
$olx->users()->accountBalance(): array;   // wallet / bonus / refund credits
$olx->users()->paymentMethods(): array;   // available methods (account, postpaid)
$olx->users()->billing(int $page = 1, ?int $limit = null): array;
$olx->users()->prepaidInvoices(int $page = 1, ?int $limit = null): array;
$olx->users()->postpaidInvoices(int $page = 1, ?int $limit = null): array;
```

### Packets — `$olx->packets()`

```php
$olx->packets()->getAll(int $category_id, string $payment_method, ?string $type = null, ?bool $with_features = null, ?int $zone_id = null): array;
$olx->packets()->zones(int $category_id): array;
$olx->packets()->userPackets(int $offset = 0, ?int $limit = null, ?string $availability = null, ?string $sort_by = null): array;
$olx->packets()->buyForUser(array $params): array;            // POST /users/me/packets
$olx->packets()->buyForAdvert(int $advert_id, array $params): array; // POST /adverts/{id}/packets
```

### Paid features — `$olx->paidFeatures()`

```php
$olx->paidFeatures()->getAll(): array;                          // promotions, top ads, …
$olx->paidFeatures()->forAdvert(int $advert_id): array;
$olx->paidFeatures()->buyForAdvert(int $advert_id, array $params): array; // {code, payment_method}
```

### Business profile — `$olx->usersBusiness()`

```php
$olx->usersBusiness()->me(): array;
$olx->usersBusiness()->update(array $params): array;
$olx->usersBusiness()->logos(): array;
$olx->usersBusiness()->addLogo(array $params): array;
$olx->usersBusiness()->deleteLogo(int $logo_id): void;
$olx->usersBusiness()->banners(): array;
$olx->usersBusiness()->addBanner(array $params): array;
$olx->usersBusiness()->deleteBanner(int $banner_id): void;
```

### Threads & messages

```php
$olx->threads()->get(int $thread_id): array;
$olx->threads()->getAll(int $offset = 0, ?int $limit = null, ?int $advert_id = null, ?int $interlocutor_id = null): array;
$olx->threads()->markAsRead(int $thread_id): void;
$olx->threads()->setFavourite(int $thread_id): void;
$olx->threads()->unsetFavourite(int $thread_id): void;
$olx->threads()->post(int $thread_id, string $text): void;

$olx->messages()->get(int $thread_id, int $offset = 0, ?int $limit = null): array;
$olx->messages()->getOne(int $thread_id, int $message_id): array;
```

### Payments & error handling

To avoid the payment errors described in the OLX docs (insufficient credits,
invalid payment method, missing packet variant), check state before purchasing:

```php
$balance = $olx->users()->accountBalance();        // "Not enough credits"
$methods = $olx->users()->paymentMethods();         // is "postpaid" activated?
$packets = $olx->packets()->getAll($categoryId, 'account'); // packet variant exists?

$olx->packets()->buyForAdvert($advertId, ['payment_method' => 'account']);
```

## Error handling

Every HTTP error is mapped to a typed exception. All of them extend
`Parhomenko\Olx\Exceptions\BaseOlxException` and expose `getTitle()` and
`getDetail()`.

| Status | Exception                       |
|--------|---------------------------------|
| 400    | `BadRequestException`           |
| 400    | `ValidationException` (when the body contains validation errors; adds `getValidation()`) |
| 401    | `UnauthorizedException`         |
| 403    | `ForbiddenException`            |
| 404    | `NotFoundException`             |
| 406    | `NotAcceptableException`        |
| 415    | `UnsupportedMediaTypeException` |
| 429    | `CallLimitException`            |
| 5xx    | `ServerException`               |
| other  | `BadRequestException`           |

```php
use Parhomenko\Olx\Exceptions\ValidationException;
use Parhomenko\Olx\Exceptions\CallLimitException;
use Parhomenko\Olx\Exceptions\BaseOlxException;

try {
    $olx->adverts()->create($payload);
} catch (ValidationException $e) {
    $errors = $e->getValidation();
} catch (CallLimitException $e) {
    // back off and retry later
} catch (BaseOlxException $e) {
    // any other OLX API error
    error_log($e->getTitle() . ': ' . $e->getDetail());
}
```

## Supported countries

The `Parhomenko\Olx\Country` enum is the single source of truth:

| Case          | Code | Base URI              |
|---------------|------|-----------------------|
| `Country::UA` | `ua` | `https://www.olx.ua/` |
| `Country::PL` | `pl` | `https://www.olx.pl/` |
| `Country::BG` | `bg` | `https://www.olx.bg/` |
| `Country::RO` | `ro` | `https://www.olx.ro/` |
| `Country::KZ` | `kz` | `https://www.olx.kz/` |
| `Country::PT` | `pt` | `https://www.olx.pt/` |

## Upgrading from 2.x to 3.0

3.0 is a breaking release. The most common migrations:

### 1. PHP 8.1+ is now required

The minimum supported version moved from PHP 7.0 to **8.1** (the `Country` enum
relies on it).

### 2. `OlxFactory::get()` signature

```php
// 2.x
OlxFactory::get('ua', $credentials);

// 3.0 — positional string calls still work, enum is recommended
OlxFactory::get(Country::UA, $credentials);
OlxFactory::get('ua', $credentials); // still valid
```

The first parameter was renamed `$country_code` → `$country` and is now typed
`string|Country`. Only **named-argument** calls (`get(country_code: 'ua')`) need
updating to `get(country: 'ua')`.

### 3. Typed exceptions everywhere

In 2.x several resources (categories, cities, regions, districts, currencies,
languages, users, threads, messages) let raw `GuzzleHttp\Exception\*` propagate.
In 3.0 **all** resources throw the typed `Parhomenko\Olx\Exceptions\*`
hierarchy (see the table above). Replace any `catch (GuzzleHttp\Exception\ClientException ...)`
around these calls with the relevant OLX exception (or the `BaseOlxException`
base type).

### 4. Advert deletion changed

```php
// 2.x
$olx->adverts()->delete_notactive($id); // DELETE only
$olx->adverts()->delete($id);           // deactivated, then deleted

// 3.0
$olx->adverts()->delete($id);     // single DELETE (replaces delete_notactive)

$olx->adverts()->deactivate($id); // call explicitly if you need the old
$olx->adverts()->delete($id);     // deactivate-then-delete behaviour
```

`delete_notactive()` was removed; `delete()` now performs a single `DELETE`
request and no longer deactivates the advert first.

### 5. Thread methods renamed to camelCase

The snake_case thread methods `mark_as_read()`, `set_favourite()` and
`unset_favourite()` were **removed**. Use the camelCase equivalents
`markAsRead()`, `setFavourite()` and `unsetFavourite()`.

### 6. `Messages::get()` now paginates

`offset` / `limit` arguments were previously ignored and are now sent to the
API. If you relied on always receiving the first page, pass the arguments
explicitly.

### 7. New facade accessors

`$olx->threads()` and `$olx->messages()` are now reachable from the facade
(the classes existed in 2.x but could not be obtained through `Api`).

### 8. Authentication via `authenticator()`

The OAuth/token class is `Parhomenko\Olx\Api\Authenticator` (it handles
authentication, not user data — that is the separate `Users` resource), reached
through `$olx->authenticator()`:

```php
// 2.x
$olx->user()->getOAuthLink(...);
$olx->user()->refreshToken();

// 3.0
$olx->authenticator()->getOAuthLink(...);
$olx->authenticator()->refreshToken();
```

### 9. `OlxApiInterface`

The facade interface is `OlxApiInterface` (no Hungarian `I` prefix). Replace any
`IOlxApi` type hints with `OlxApiInterface`.

### 10. Command methods return `void`

Methods that signal success/failure (and previously returned an always-`true`
`bool`) now return `void` and throw a `BaseOlxException` on failure:
`Adverts::activate/deactivate/delete/deleteStatistic/deleteLogo`, all `Threads`
command methods, and `UsersBusiness::deleteLogo/deleteBanner`.

```php
// before
if ($olx->adverts()->activate($id)) { ... }

// 3.0 — success = no exception thrown
$olx->adverts()->activate($id);
```

### 11. `Credentials` value object (optional)

Credentials may be passed as a typed `Parhomenko\Olx\Credentials` object; the
associative-array form still works (normalised via `Credentials::fromArray()`):

```php
use Parhomenko\Olx\Credentials;

$olx = OlxFactory::get(Country::UA, new Credentials(
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret',
));
```

## Development

```bash
composer install
composer test      # PHPUnit
composer phpstan   # static analysis (PHPStan level 8)
composer cs        # check coding style (PSR-12, dry-run)
composer cs-fix    # auto-fix coding style
```

## License

[MIT](LICENSE)
