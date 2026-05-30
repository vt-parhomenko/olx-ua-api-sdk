<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Tests\Exceptions;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException as GuzzleServerException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Parhomenko\Olx\Exceptions\BadRequestException;
use Parhomenko\Olx\Exceptions\CallLimitException;
use Parhomenko\Olx\Exceptions\ExceptionFactory;
use Parhomenko\Olx\Exceptions\ForbiddenException;
use Parhomenko\Olx\Exceptions\NotAcceptableException;
use Parhomenko\Olx\Exceptions\NotFoundException;
use Parhomenko\Olx\Exceptions\ServerException;
use Parhomenko\Olx\Exceptions\UnauthorizedException;
use Parhomenko\Olx\Exceptions\UnsupportedMediaTypeException;
use Parhomenko\Olx\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class ExceptionFactoryTest extends TestCase
{
    /**
     * @param mixed $body
     */
    private function clientError(int $status, $body = ''): ClientException
    {
        return new ClientException(
            'HTTP ' . $status,
            new Request('GET', '/x'),
            new Response($status, [], is_string($body) ? $body : json_encode($body))
        );
    }

    /**
     * @param mixed $body
     */
    private function serverError(int $status, $body = ''): GuzzleServerException
    {
        return new GuzzleServerException(
            'HTTP ' . $status,
            new Request('GET', '/x'),
            new Response($status, [], is_string($body) ? $body : json_encode($body))
        );
    }

    public function testValidationErrorIsMapped(): void
    {
        try {
            ExceptionFactory::throw($this->clientError(400, [
                'error' => [
                    'title' => 'Validation Failed',
                    'detail' => 'Some fields are invalid',
                    'validation' => [ [ 'field' => 'title', 'error' => 'required' ] ],
                ],
            ]));

            $this->fail('Expected ValidationException to be thrown');
        } catch (ValidationException $e) {
            $this->assertSame('Some fields are invalid', $e->getMessage());
            $this->assertNotEmpty($e->getValidation());
        }
    }

    public function testBadRequestWithoutValidation(): void
    {
        try {
            ExceptionFactory::throw($this->clientError(400, [
                'error' => [ 'title' => 'Bad request', 'detail' => 'Nope' ],
            ]));

            $this->fail('Expected BadRequestException to be thrown');
        } catch (BadRequestException $e) {
            $this->assertSame('Bad request', $e->getMessage());
            $this->assertSame('Bad request', $e->getTitle());
            $this->assertSame('Nope', $e->getDetail());
        }
    }

    public function testUnauthorized(): void
    {
        $this->expectException(UnauthorizedException::class);
        ExceptionFactory::throw($this->clientError(401, [ 'error_description' => 'token expired' ]));
    }

    public function testForbidden(): void
    {
        $this->expectException(ForbiddenException::class);
        ExceptionFactory::throw($this->clientError(403));
    }

    public function testNotFound(): void
    {
        try {
            ExceptionFactory::throw($this->clientError(404, [ 'error' => [ 'title' => 't', 'detail' => 'd' ] ]));

            $this->fail('Expected NotFoundException to be thrown');
        } catch (NotFoundException $e) {
            // title/detail must map straight through, consistently with 400.
            $this->assertSame('t', $e->getTitle());
            $this->assertSame('d', $e->getDetail());
        }
    }

    public function testNotAcceptable(): void
    {
        $this->expectException(NotAcceptableException::class);
        ExceptionFactory::throw($this->clientError(406));
    }

    public function testUnsupportedMediaType(): void
    {
        $this->expectException(UnsupportedMediaTypeException::class);
        ExceptionFactory::throw($this->clientError(415));
    }

    public function testCallLimit(): void
    {
        $this->expectException(CallLimitException::class);
        ExceptionFactory::throw($this->clientError(429));
    }

    public function testServerError(): void
    {
        $this->expectException(ServerException::class);
        ExceptionFactory::throw($this->serverError(500));
    }

    public function testUnmapped5xxFallsBackToServerError(): void
    {
        $this->expectException(ServerException::class);
        ExceptionFactory::throw($this->serverError(503));
    }

    public function testUnmapped4xxFallsBackToBadRequest(): void
    {
        $this->expectException(BadRequestException::class);
        ExceptionFactory::throw($this->clientError(409, [ 'error' => [ 'title' => 'Conflict' ] ]));
    }
}
