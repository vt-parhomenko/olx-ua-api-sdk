<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests;

use Parhomenko\Olx\Credentials;
use Parhomenko\Olx\Exceptions\MissingCredentialsException;
use PHPUnit\Framework\TestCase;

class CredentialsTest extends TestCase
{
    public function testFromArrayThrowsWhenRequiredMissing(): void
    {
        $this->expectException(MissingCredentialsException::class);
        $this->expectExceptionMessage('Missing credentials');

        Credentials::fromArray([ 'client_id' => 'only-id' ]);
    }

    public function testFromArrayMapsFields(): void
    {
        $credentials = Credentials::fromArray([
            'client_id' => 'id',
            'client_secret' => 'secret',
            'access_token' => 'abc',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);

        $this->assertSame('id', $credentials->clientId);
        $this->assertSame('secret', $credentials->clientSecret);
        $this->assertSame('abc', $credentials->accessToken);
        $this->assertSame('Bearer', $credentials->tokenType);
        $this->assertSame(3600, $credentials->expiresIn);
        $this->assertSame(Credentials::DEFAULT_SCOPE, $credentials->scope);
    }

    public function testConstructorAppliesDefaults(): void
    {
        $credentials = new Credentials('id', 'secret');

        $this->assertNull($credentials->accessToken);
        $this->assertNull($credentials->refreshToken);
        $this->assertSame(Credentials::DEFAULT_TOKEN_TYPE, $credentials->tokenType);
        $this->assertSame(Credentials::DEFAULT_GRANT_TYPE, $credentials->grantType);
        $this->assertSame(0, $credentials->expiresIn);
    }

    public function testFromArrayThrowsWhenRequiredIsNull(): void
    {
        $this->expectException(MissingCredentialsException::class);

        Credentials::fromArray([ 'client_id' => null, 'client_secret' => 'secret' ]);
    }

    public function testFromArrayThrowsWhenRequiredIsEmptyString(): void
    {
        $this->expectException(MissingCredentialsException::class);

        Credentials::fromArray([ 'client_id' => 'id', 'client_secret' => '' ]);
    }
}
