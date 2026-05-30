<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests\Api;

use DateTimeImmutable;
use DateTimeZone;
use GuzzleHttp\Client;
use Parhomenko\Olx\Api\Authenticator;
use Parhomenko\Olx\Credentials;
use Parhomenko\Olx\Tests\OlxTestCase;

/**
 * An Authenticator test double with a fixed, controllable "current time".
 */
class FixedClockAuthenticator extends Authenticator
{
    private DateTimeImmutable $fixed_now;

    public function __construct(Client $guzzleClient, Credentials $credentials, string $base_uri, DateTimeImmutable $fixed_now)
    {
        parent::__construct($guzzleClient, $credentials, $base_uri);
        $this->fixed_now = $fixed_now;
    }

    protected function now(): DateTimeImmutable
    {
        return $this->fixed_now;
    }
}

class AuthenticatorTest extends OlxTestCase
{
    public function testGettersExposeCredentials(): void
    {
        $client = $this->mockClient([]);

        $authenticator = new Authenticator($client, new Credentials(
            clientId: 'id',
            clientSecret: 'secret',
            accessToken: 'abc',
            tokenType: 'Bearer',
        ), 'https://www.olx.ua/');

        $this->assertSame('abc', $authenticator->getAccessToken());
        $this->assertSame('Bearer', $authenticator->getTokenType());
    }

    public function testGetOAuthLinkContainsClientId(): void
    {
        $client = $this->mockClient([]);

        $authenticator = new Authenticator($client, new Credentials(
            clientId: 'my-client',
            clientSecret: 'secret',
        ), 'https://www.olx.ua/');

        $link = $authenticator->getOAuthLink('https://app.test/callback', 'state123');

        $this->assertStringContainsString('https://www.olx.ua/oauth/authorize/?', $link);
        $this->assertStringContainsString('client_id=my-client', $link);
        $this->assertStringContainsString('response_type=code', $link);
        $this->assertStringContainsString('redirect_uri=', $link);
        $this->assertStringContainsString('state123', $link);
    }

    public function testGetOAuthLinkHonorsCustomScope(): void
    {
        $client = $this->mockClient([]);

        $authenticator = new Authenticator($client, new Credentials(
            clientId: 'my-client',
            clientSecret: 'secret',
            scope: 'read v2',
        ), 'https://www.olx.ua/');

        $link = $authenticator->getOAuthLink();

        $this->assertStringContainsString('scope=' . rawurlencode('read v2'), $link);
    }

    public function testCheckTokenRefreshesWhenAccessTokenMissing(): void
    {
        $client = $this->mockClient([
            $this->jsonResponse(200, [
                'access_token' => 'fresh-token',
                'token_type' => 'bearer',
                'refresh_token' => 'fresh-refresh',
                'expires_in' => 3600,
            ]),
        ]);

        $now = new DateTimeImmutable('2026-05-29 12:00:00');

        $authenticator = new FixedClockAuthenticator($client, new Credentials(
            clientId: 'id',
            clientSecret: 'secret',
            refreshToken: 'old-refresh',
        ), 'https://www.olx.ua/', $now);

        $this->assertNull($authenticator->getAccessToken());

        $authenticator->checkToken();

        $this->assertSame('fresh-token', $authenticator->getAccessToken());
        $this->assertSame('fresh-refresh', $authenticator->getRefreshToken());
        $this->assertSame(3600, $authenticator->getTokenExpiresIn());
        $this->assertCount(1, $this->history);
        $this->assertStringContainsString(Authenticator::OLX_AUTH_REQUEST_URI, (string) $this->history[0]['request']->getUri());
    }

    public function testCheckTokenRefreshesWhenTokenExpired(): void
    {
        $client = $this->mockClient([
            $this->jsonResponse(200, [
                'access_token' => 'fresh-token',
                'token_type' => 'bearer',
                'refresh_token' => 'fresh-refresh',
                'expires_in' => 3600,
            ]),
        ]);

        $now = new DateTimeImmutable('2026-05-29 12:00:00');

        // Token issued two hours ago with a one-hour lifetime: long expired.
        $authenticator = new FixedClockAuthenticator($client, new Credentials(
            clientId: 'id',
            clientSecret: 'secret',
            accessToken: 'stale-token',
            refreshToken: 'old-refresh',
            expiresIn: 3600,
            updatedAt: '2026-05-29 10:00:00',
        ), 'https://www.olx.ua/', $now);

        $authenticator->checkToken();

        $this->assertSame('fresh-token', $authenticator->getAccessToken());
        $this->assertCount(1, $this->history);
    }

    public function testCheckTokenRefreshesWithinExpiryBuffer(): void
    {
        $client = $this->mockClient([
            $this->jsonResponse(200, [
                'access_token' => 'fresh-token',
                'token_type' => 'bearer',
                'refresh_token' => 'fresh-refresh',
                'expires_in' => 3600,
            ]),
        ]);

        $now = new DateTimeImmutable('2026-05-29 12:00:00');

        // Issued 3570s ago with a 3600s lifetime => 30s until expiry, inside the 60s buffer.
        $authenticator = new FixedClockAuthenticator($client, new Credentials(
            clientId: 'id',
            clientSecret: 'secret',
            accessToken: 'about-to-expire',
            refreshToken: 'old-refresh',
            expiresIn: 3600,
            updatedAt: $now->modify('-3570 seconds')->format('Y-m-d H:i:s'),
        ), 'https://www.olx.ua/', $now);

        $authenticator->checkToken();

        $this->assertSame('fresh-token', $authenticator->getAccessToken());
        $this->assertCount(1, $this->history);
    }

    public function testCheckTokenDoesNotRefreshWhenTokenValid(): void
    {
        // No responses queued: a refresh attempt would throw on the empty MockHandler.
        $client = $this->mockClient([]);

        $now = new DateTimeImmutable('2026-05-29 12:00:00');

        // Issued 100s ago with a 3600s lifetime => well outside the 60s buffer.
        $authenticator = new FixedClockAuthenticator($client, new Credentials(
            clientId: 'id',
            clientSecret: 'secret',
            accessToken: 'still-valid',
            refreshToken: 'old-refresh',
            expiresIn: 3600,
            updatedAt: $now->modify('-100 seconds')->format('Y-m-d H:i:s'),
        ), 'https://www.olx.ua/', $now);

        $authenticator->checkToken();

        $this->assertSame('still-valid', $authenticator->getAccessToken());
        $this->assertCount(0, $this->history);
    }

    public function testAuthenticateAsClientUsesClientCredentialsGrant(): void
    {
        $client = $this->mockClient([
            $this->jsonResponse(200, [
                'access_token' => 'client-token',
                'token_type' => 'bearer',
                'expires_in' => 3600,
            ]),
        ]);

        $authenticator = new Authenticator($client, new Credentials(
            clientId: 'id',
            clientSecret: 'secret',
        ), 'https://www.olx.ua/');

        $authenticator->authenticateAsClient();

        $this->assertSame('client-token', $authenticator->getAccessToken());

        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertStringContainsString(Authenticator::OLX_AUTH_REQUEST_URI, (string) $request->getUri());

        $body = json_decode((string) $request->getBody(), true);
        $this->assertSame('client_credentials', $body['grant_type']);
    }

    public function testRefreshStoresOffsetAwareTimestamp(): void
    {
        $client = $this->mockClient([
            $this->jsonResponse(200, [
                'access_token' => 'fresh-token',
                'token_type' => 'bearer',
                'refresh_token' => 'fresh-refresh',
                'expires_in' => 3600,
            ]),
        ]);

        $now = new DateTimeImmutable('2026-05-29 12:00:00', new DateTimeZone('+02:00'));

        $authenticator = new FixedClockAuthenticator($client, new Credentials(
            clientId: 'id',
            clientSecret: 'secret',
            refreshToken: 'old-refresh',
        ), 'https://www.olx.ua/', $now);

        $authenticator->checkToken();

        // Stored as offset-aware ISO-8601, so expiry is unambiguous across timezones.
        $this->assertSame('2026-05-29T12:00:00+02:00', $authenticator->getTokenUpdatedAt());
    }

    public function testTokenResponseWithoutOptionalFieldsKeepsDefaults(): void
    {
        // A spec-minimal token response carrying only access_token: token_type
        // and expires_in are absent. Must not raise a warning or TypeError.
        $client = $this->mockClient([
            $this->jsonResponse(200, [ 'access_token' => 'minimal-token' ]),
        ]);

        $authenticator = new Authenticator($client, new Credentials(
            clientId: 'id',
            clientSecret: 'secret',
        ), 'https://www.olx.ua/');

        $authenticator->authenticateAsClient();

        $this->assertSame('minimal-token', $authenticator->getAccessToken());
        $this->assertSame(Credentials::DEFAULT_TOKEN_TYPE, $authenticator->getTokenType());
        $this->assertSame(0, $authenticator->getTokenExpiresIn());
    }
}
