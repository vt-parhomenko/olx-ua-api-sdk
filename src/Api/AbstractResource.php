<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Api;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Parhomenko\Olx\Exceptions\BaseOlxException;
use Parhomenko\Olx\Exceptions\CallLimitException;
use Parhomenko\Olx\Exceptions\ForbiddenException;
use Parhomenko\Olx\Exceptions\NotAcceptableException;
use Parhomenko\Olx\Exceptions\NotFoundException;
use Parhomenko\Olx\Exceptions\ServerException;
use Parhomenko\Olx\Exceptions\UnauthorizedException;
use Parhomenko\Olx\Exceptions\UnsupportedMediaTypeException;
use Parhomenko\Olx\Exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Parhomenko\Olx\Exceptions\BadRequestException;
use Parhomenko\Olx\Exceptions\ExceptionFactory;

/**
 * Base class for every OLX API resource.
 *
 * Holds the shared HTTP plumbing (authenticated request, response
 * decoding, status assertions) so concrete resources only describe
 * the endpoints they expose.
 */
abstract class AbstractResource
{
    public const API_VERSION = '2.0';

    public function __construct(protected Authenticator $authenticator, protected ClientInterface $guzzleClient)
    {
    }

    /**
     * Perform an authenticated request against the OLX API.
     *
     * The Authorization and Version headers are injected automatically
     * and any HTTP error response is mapped onto a typed OLX exception.
     *
     * @throws GuzzleException
     * @throws BadRequestException
     * @throws CallLimitException
     * @throws ForbiddenException
     * @throws NotAcceptableException
     * @throws NotFoundException
     * @throws ServerException
     * @throws UnauthorizedException
     * @throws UnsupportedMediaTypeException
     * @throws ValidationException
     */
    protected function request(string $method, string $uri, array $options = []): ResponseInterface
    {
        $options['headers'] = array_merge([
            'Authorization' => $this->authenticator->getTokenType() . ' ' . $this->authenticator->getAccessToken(),
            'Version' => static::API_VERSION,
        ], $options['headers'] ?? []);

        try {
            return $this->guzzleClient->request($method, $uri, $options);
        } catch (BadResponseException $e) {
            ExceptionFactory::throw($e);
        }
    }

    /**
     * Perform a request and return the "data" envelope of the JSON body.
     *
     * @throws GuzzleException
     * @throws BaseOlxException any mapped API error, incl. BadRequestException on an empty response
     */
    protected function fetchData(string $method, string $uri, array $options = [], string $context = ''): array
    {
        $response = $this->request($method, $uri, $options);

        $decoded = json_decode((string) $response->getBody(), true);

        if (!isset($decoded['data'])) {
            throw new BadRequestException('Got empty or invalid response' . ($context !== '' ? ' | ' . $context : ''));
        }

        return $decoded['data'];
    }

    /**
     * Assert that a command-style request returned the expected status code.
     *
     * @throws BadRequestException when the status code does not match
     */
    protected function expectStatus(ResponseInterface $response, int $status = 204): void
    {
        if ($response->getStatusCode() !== $status) {
            throw new BadRequestException((string) $response->getBody());
        }
    }
}
