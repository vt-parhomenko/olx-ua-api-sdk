# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.1.0]

### Added

- Optional `array $httpOptions` argument on `Api::__construct()` and `OlxFactory::get()`: Guzzle client
  options (`timeout`, `connect_timeout`, `handler`, `proxy`, …) applied to every request, including the OAuth
  token requests. A `base_uri` key in the options is ignored — the country/base URI argument always wins.
- `Api::getHttpClient()` returns the underlying HTTP client (e.g. to inspect its configuration).

### Changed

- `AbstractResource` and `Authenticator` accept any `GuzzleHttp\ClientInterface` instead of the concrete
  `GuzzleHttp\Client` (type widening; existing callers are unaffected).

Without `$httpOptions` the client is configured exactly as in 3.0.0 (no timeouts — Guzzle waits indefinitely),
so the release is backward compatible.

## [3.0.0]

3.0 is a breaking release relative to 2.x. See the "Upgrading from 2.x to 3.0"
section of the README for the full migration guide.

### Added

- Full coverage of the OLX Partner API 2.0.
  - New resources: `Packets`, `PaidFeatures`, `Locations`, `UsersBusiness`.
  - `Users`: `accountBalance()`, `paymentMethods()`, `billing()`, `prepaidInvoices()`, `postpaidInvoices()`.
  - `Adverts`: `statistics()`, `deleteStatistic()`, `moderationReason()`, `logos()`, `addLogo()`, `deleteLogo()`.
  - `Categories::suggestion()`, `Cities::districts()`, `Messages::getOne()`.
  - Facade accessors: `packets()`, `paidFeatures()`, `locations()`, `usersBusiness()`.
- `Country` enum as the single source of truth for supported countries and base URIs.
- Typed exception hierarchy (`BaseOlxException` and subclasses) mapped from HTTP status codes via `ExceptionFactory`.
- `Credentials` value object; `Api` and `OlxFactory::get()` accept `Credentials|array`.
- `Authenticator::authenticateAsClient()` — `client_credentials` grant for read-only catalog access without a user context.
- `Authenticator::getTokenUpdatedAt()` to read back the SDK-managed token timestamp for persistence.
- A safety buffer (`Authenticator::TOKEN_EXPIRY_BUFFER`) so `checkToken()` refreshes shortly before expiry.

### Changed

- Minimum PHP version raised to 8.1; `guzzlehttp/guzzle` requirement is now `^7.5` (dropped end-of-life Guzzle 6).
- The OAuth/token class is `Api\Authenticator`, reached via `Api::authenticator()` (it handles authentication, not user data — see the `Users` resource).
- The facade interface is `OlxApiInterface`.
- Command methods return `void` and throw on failure instead of an always-`true` `bool`: `Adverts::activate/deactivate/delete/deleteStatistic/deleteLogo`, all `Threads` command methods, and `UsersBusiness::deleteLogo/deleteBanner`.
- `OlxFactory::get()` accepts `string|Country`; the first parameter was renamed `$country_code` → `$country`.
- All resources throw the typed `Parhomenko\Olx\Exceptions\*` hierarchy instead of leaking raw Guzzle exceptions.
- Thread command methods use camelCase: `markAsRead()`, `setFavourite()`, `unsetFavourite()`.
- `Messages::get()` now sends the `offset`/`limit` pagination parameters.
- All source files declare `strict_types=1`; properties are typed and use constructor promotion.
- `ExceptionFactory::throw()` is typed `never`; `MissingCredentialsException` replaces the generic exception for missing credentials.
- The OAuth authorization link is built from an explicit base URI instead of the deprecated `Client::getConfig()`.
- Enforced the PSR-12 coding standard via php-cs-fixer and PHPStan level 8.

### Removed

- The deprecated snake_case thread methods `mark_as_read()`, `set_favourite()` and `unset_favourite()`; use `markAsRead()`, `setFavourite()` and `unsetFavourite()`.
- `Adverts::delete_notactive()`; `Adverts::delete()` now performs a single `DELETE` request without deactivating first.

### Fixed

- `Authenticator` records the token timestamp as offset-aware ISO-8601 (was a naive `Y-m-d H:i:s` string), so expiry is evaluated correctly when tokens are persisted on one host/timezone and read on another.
- Token responses that omit `token_type`/`expires_in` no longer raise a `TypeError` or warning; the previous values are kept.
- `Credentials::fromArray()` rejects `null`/empty `client_id` or `client_secret` instead of silently building empty credentials.
- `Adverts::getAll()` no longer drops a `'0'` string `external_id`.
- `ExceptionFactory` maps the 404 `title`/`detail` fields in the correct order (they were swapped), consistently with the other statuses.
