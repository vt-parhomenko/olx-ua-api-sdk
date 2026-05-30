<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Exceptions;

use Exception;
use GuzzleHttp\Exception\RequestException;

class ExceptionFactory
{
    /**
     * Maps a Guzzle HTTP error onto a typed OLX exception.
     * Guarantees that something is always thrown so callers with an
     * array return type never fall through to an implicit null return.
     *
     * @param Exception $e
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
    public static function throw(Exception  $e): never
    {
        $response = null;

        if ($e instanceof RequestException && $e->hasResponse()) {
            $response = json_decode((string) $e->getResponse()?->getBody());
        }

        switch ($e->getCode()) {
            case 400:
                if ($response && isset($response->error) && !empty($response->error->validation)) {
                    throw new ValidationException($response->error->detail ?? $e->getMessage(), $e->getCode(), null, $response->error->title ?? null, $response->error->detail ?? null, (array) ($response->error->validation ?? []));
                }

                throw new BadRequestException($response->error->title ?? $e->getMessage(), $e->getCode(), null, $response->error->title ?? null, $response->error->detail ?? null);
            case 401:
                throw new UnauthorizedException($response->error_description ?? $e->getMessage(), $e->getCode(), null, $response->error_description ?? null, $response->error_human_title ?? null);
            case 403:
                throw new ForbiddenException($e->getMessage(), $e->getCode());
            case 404:
                throw new NotFoundException($response->error->detail ?? $e->getMessage(), $e->getCode(), null, $response->error->title ?? null, $response->error->detail ?? null);
            case 406:
                throw new NotAcceptableException($e->getMessage(), $e->getCode());
            case 415:
                throw new UnsupportedMediaTypeException($e->getMessage(), $e->getCode());
            case 429:
                throw new CallLimitException($e->getMessage(), $e->getCode());
            default:
                if ($e->getCode() >= 500) {
                    throw new ServerException($e->getMessage(), $e->getCode());
                }

                throw new BadRequestException($response->error->title ?? $e->getMessage(), $e->getCode(), null, $response->error->title ?? null, $response->error->detail ?? null);
        }
    }
}
