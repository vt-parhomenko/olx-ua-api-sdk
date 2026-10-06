<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Api;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Query;
use Parhomenko\Olx\Credentials;
use Parhomenko\Olx\Exceptions\BadRequestException;
use Parhomenko\Olx\Exceptions\BaseOlxException;
use Parhomenko\Olx\Exceptions\CallLimitException;
use Parhomenko\Olx\Exceptions\ExceptionFactory;
use Parhomenko\Olx\Exceptions\ForbiddenException;
use Parhomenko\Olx\Exceptions\NotAcceptableException;
use Parhomenko\Olx\Exceptions\NotFoundException;
use Parhomenko\Olx\Exceptions\RefreshTokenException;
use Parhomenko\Olx\Exceptions\ServerException;
use Parhomenko\Olx\Exceptions\UnauthorizedException;
use Parhomenko\Olx\Exceptions\UnsupportedMediaTypeException;
use Parhomenko\Olx\Exceptions\ValidationException;

/**
 * OLX OAuth client: builds authorization links and obtains, refreshes and
 * validates access tokens. This is the authentication layer, not the user
 * data resource (see {@see Users}).
 */
class Authenticator
{
    public const OLX_AUTH_REQUEST_URI = '/api/open/oauth/token';

    /**
     * Safety buffer (seconds) before expiry at which the token is refreshed proactively.
     */
    public const TOKEN_EXPIRY_BUFFER = 60;

    private ClientInterface $guzzleClient;
    private string $base_uri;
    private string $client_id;
    private string $client_secret;
    private ?string $access_token;
    private ?string $refresh_token;
    private string $token_type;
    private int $token_expires_in;
    private ?string $token_updated_at;
    private string $grant_type;
    private string $scope;

    public function __construct(ClientInterface $guzzleClient, Credentials $credentials, string $base_uri)
    {
        $this->guzzleClient = $guzzleClient;
        $this->base_uri = $base_uri;

        $this->client_id = $credentials->clientId;
        $this->client_secret = $credentials->clientSecret;
        $this->access_token = $credentials->accessToken;
        $this->refresh_token = $credentials->refreshToken;
        $this->token_type = $credentials->tokenType;
        $this->grant_type = $credentials->grantType;
        $this->scope = $credentials->scope;
        $this->token_expires_in = $credentials->expiresIn;
        $this->token_updated_at = $credentials->updatedAt;
    }

    /**
     * Returns the current time. Overridable seam for testing.
     */
    protected function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function getClientId(): string
    {
        return $this->client_id;
    }

    public function getClientSecret(): string
    {
        return $this->client_secret;
    }

    public function getTokenType(): string
    {
        return $this->token_type;
    }

    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refresh_token;
    }

    public function getTokenExpiresIn(): int
    {
        return $this->token_expires_in;
    }

    /**
     * Timestamp (ISO-8601, offset-aware) of when the current token was last
     * issued or refreshed. Persist this together with the tokens so expiry can
     * be re-evaluated unambiguously on any host, regardless of its timezone.
     */
    public function getTokenUpdatedAt(): ?string
    {
        return $this->token_updated_at;
    }

    /**
     * Assigns the token fields from a decoded token response.
     *
     * @param array<string, mixed> $data decoded token response
     * @param bool $overwrite_refresh_token_only_if_present when true, refresh_token is only
     *        overwritten if present in the response (used by authorize())
     */
    private function applyTokenResponse(array $data, bool $overwrite_refresh_token_only_if_present = false): void
    {
        $this->access_token = $data['access_token'];
        $this->token_type = $data['token_type'] ?? $this->token_type;

        if ($overwrite_refresh_token_only_if_present) {
            if (!empty($data['refresh_token'])) {
                $this->refresh_token = $data['refresh_token'];
            }
        } else {
            $this->refresh_token = $data['refresh_token'] ?? $this->refresh_token;
        }

        $this->token_expires_in = (int) ($data['expires_in'] ?? 0);
        $this->token_updated_at = $this->now()->format(DateTimeInterface::ATOM);
    }

    /**
     * Refresh the access token if it is missing or has expired.
     *
     * @throws GuzzleException on a transport/connection error
     * @throws RefreshTokenException on HTTP 400 (invalid or expired refresh token)
     * @throws BadRequestException on an empty/invalid response
     * @throws UnauthorizedException on HTTP 401
     * @throws ForbiddenException on HTTP 403
     * @throws NotFoundException on HTTP 404
     * @throws NotAcceptableException on HTTP 406
     * @throws UnsupportedMediaTypeException on HTTP 415
     * @throws CallLimitException on HTTP 429
     * @throws ServerException on HTTP 5xx
     * @throws BaseOlxException any other OLX API error
     * @throws Exception on an invalid stored token timestamp
     */
    public function checkToken(): self
    {
        if (!$this->access_token || $this->token_updated_at === null) {
            $this->refreshToken();
        } else {
            $date_time_expires = new DateTimeImmutable($this->token_updated_at);
            $date_time_expires = $date_time_expires->add(new DateInterval('PT' . $this->token_expires_in . 'S'));
            $date_time_expires = $date_time_expires->sub(new DateInterval('PT' . self::TOKEN_EXPIRY_BUFFER . 'S'));

            if ($date_time_expires <= $this->now()) {
                $this->refreshToken();
            }
        }

        return $this;
    }

    /**
     * Step 1. Build the OAuth authorization URL.
     */
    public function getOAuthLink(?string $redirect_uri = null, ?string $state = null): string
    {
        $params = [
            'client_id' => $this->client_id,
            'response_type' => 'code',
            'scope' => $this->scope,
        ];

        if (!is_null($redirect_uri)) {
            $params['redirect_uri'] = $redirect_uri;
        }
        if (!is_null($state)) {
            $params['state'] = $state;
        }

        return $this->base_uri . 'oauth/authorize/?' . Query::build($params);
    }

    /**
     * Step 2. Exchange an authorization code for an access token.
     *
     * @throws GuzzleException on a transport/connection error
     * @throws BadRequestException on HTTP 400, or an empty/invalid response
     * @throws ValidationException on HTTP 400 with field validation errors
     * @throws UnauthorizedException on HTTP 401
     * @throws ForbiddenException on HTTP 403
     * @throws NotFoundException on HTTP 404
     * @throws NotAcceptableException on HTTP 406
     * @throws UnsupportedMediaTypeException on HTTP 415
     * @throws CallLimitException on HTTP 429
     * @throws ServerException on HTTP 5xx
     * @throws BaseOlxException any other OLX API error
     */
    public function authorize(?string $code = null, ?string $redirect_uri = null): self
    {
        try {
            $request_data = [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => $this->grant_type,
                'scope' => $this->scope,
            ];

            if (!is_null($code)) {
                $request_data['code'] = $code;
            }
            if (!is_null($redirect_uri)) {
                $request_data['redirect_uri'] = $redirect_uri;
            }

            $response = $this->guzzleClient->request('POST', self::OLX_AUTH_REQUEST_URI, [ 'json' => $request_data ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!empty($data['access_token'])) {
                $this->applyTokenResponse($data, true);
            } else {
                throw new BadRequestException('Can not get access token');
            }
        } catch (BadResponseException $e) {
            ExceptionFactory::throw($e);
        }

        return $this;
    }

    /**
     * Refresh the access token using the stored refresh token.
     *
     * @throws GuzzleException on a transport/connection error
     * @throws RefreshTokenException on HTTP 400 (invalid or expired refresh token)
     * @throws BadRequestException on an empty/invalid response
     * @throws UnauthorizedException on HTTP 401
     * @throws ForbiddenException on HTTP 403
     * @throws NotFoundException on HTTP 404
     * @throws NotAcceptableException on HTTP 406
     * @throws UnsupportedMediaTypeException on HTTP 415
     * @throws CallLimitException on HTTP 429
     * @throws ServerException on HTTP 5xx
     * @throws BaseOlxException any other OLX API error
     */
    public function refreshToken(): self
    {
        try {
            $response = $this->guzzleClient->request('POST', self::OLX_AUTH_REQUEST_URI, [ 'json' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => 'refresh_token',
                'refresh_token' => $this->refresh_token,
            ]]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!empty($data['access_token'])) {
                $this->applyTokenResponse($data);
            } else {
                throw new BadRequestException('Can not refresh access token');
            }

            return $this;
        } catch (BadResponseException $e) {
            if ($e->getCode() === 400) {
                $response = json_decode((string) $e->getResponse()->getBody());
                throw new RefreshTokenException($response->error_human_title ?? 'Can not refresh access token', $e->getCode(), null, $response->error ?? null, $response->error_description ?? null);
            }

            ExceptionFactory::throw($e);
        }
    }

    /**
     * Authenticate with the client_credentials grant for read-only access to
     * public catalog data (categories, cities, etc.) without a user context.
     *
     * @throws GuzzleException on a transport/connection error
     * @throws BadRequestException on HTTP 400, or an empty/invalid response
     * @throws ValidationException on HTTP 400 with field validation errors
     * @throws UnauthorizedException on HTTP 401
     * @throws ForbiddenException on HTTP 403
     * @throws NotFoundException on HTTP 404
     * @throws NotAcceptableException on HTTP 406
     * @throws UnsupportedMediaTypeException on HTTP 415
     * @throws CallLimitException on HTTP 429
     * @throws ServerException on HTTP 5xx
     * @throws BaseOlxException any other OLX API error
     */
    public function authenticateAsClient(): self
    {
        try {
            $response = $this->guzzleClient->request('POST', self::OLX_AUTH_REQUEST_URI, [ 'json' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => 'client_credentials',
                'scope' => $this->scope,
            ]]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!empty($data['access_token'])) {
                $this->applyTokenResponse($data, true);
            } else {
                throw new BadRequestException('Can not get client access token');
            }

            return $this;
        } catch (BadResponseException $e) {
            ExceptionFactory::throw($e);
        }
    }
}
