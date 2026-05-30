<?php

declare(strict_types=1);

namespace Parhomenko\Olx;

use Parhomenko\Olx\Exceptions\MissingCredentialsException;

/**
 * Immutable OLX OAuth credential set.
 */
final class Credentials
{
    public const DEFAULT_TOKEN_TYPE = 'bearer';
    public const DEFAULT_GRANT_TYPE = 'authorization_code';
    public const DEFAULT_SCOPE = 'read write v2';

    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly ?string $accessToken = null,
        public readonly ?string $refreshToken = null,
        public readonly string $tokenType = self::DEFAULT_TOKEN_TYPE,
        public readonly string $grantType = self::DEFAULT_GRANT_TYPE,
        public readonly string $scope = self::DEFAULT_SCOPE,
        public readonly int $expiresIn = 0,
        public readonly ?string $updatedAt = null,
    ) {
    }

    /**
     * Build credentials from the legacy associative-array format.
     *
     * @param array<string, mixed> $credentials client_id and client_secret are required
     * @throws MissingCredentialsException when a required credential is missing
     */
    public static function fromArray(array $credentials): self
    {
        $missing = [];

        foreach (['client_id', 'client_secret'] as $required) {
            if (!isset($credentials[$required]) || $credentials[$required] === '') {
                $missing[] = $required;
            }
        }

        if (!empty($missing)) {
            throw new MissingCredentialsException('Missing credentials: ' . implode(', ', $missing));
        }

        return new self(
            clientId: (string) $credentials['client_id'],
            clientSecret: (string) $credentials['client_secret'],
            accessToken: isset($credentials['access_token']) ? (string) $credentials['access_token'] : null,
            refreshToken: isset($credentials['refresh_token']) ? (string) $credentials['refresh_token'] : null,
            tokenType: isset($credentials['token_type']) ? (string) $credentials['token_type'] : self::DEFAULT_TOKEN_TYPE,
            grantType: isset($credentials['grant_type']) ? (string) $credentials['grant_type'] : self::DEFAULT_GRANT_TYPE,
            scope: isset($credentials['scope']) ? (string) $credentials['scope'] : self::DEFAULT_SCOPE,
            expiresIn: (int) ($credentials['expires_in'] ?? 0),
            updatedAt: isset($credentials['updated_at']) ? (string) $credentials['updated_at'] : null,
        );
    }
}
