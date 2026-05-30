<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Api;

use GuzzleHttp\Exception\GuzzleException;
use Parhomenko\Olx\Exceptions\BadRequestException;
use Parhomenko\Olx\Exceptions\BaseOlxException;
use Parhomenko\Olx\Exceptions\CallLimitException;
use Parhomenko\Olx\Exceptions\ForbiddenException;
use Parhomenko\Olx\Exceptions\NotAcceptableException;
use Parhomenko\Olx\Exceptions\NotFoundException;
use Parhomenko\Olx\Exceptions\ServerException;
use Parhomenko\Olx\Exceptions\UnauthorizedException;
use Parhomenko\Olx\Exceptions\UnsupportedMediaTypeException;
use Parhomenko\Olx\Exceptions\ValidationException;

class Cities extends AbstractResource
{
    public const OLX_CITIES_URL = '/api/partner/cities';

    /**
     * Get all OLX cities
     * @param int $offset
     * @param int|null $limit
     * @return array
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
    public function getAll(int $offset = 0, ?int $limit = null): array
    {
        return $this->fetchData('GET', self::OLX_CITIES_URL, [
            'query' => [ 'offset' => $offset, 'limit' => $limit ],
        ], 'Get all OLX cities');
    }

    /**
     * Get one OLX city by ID
     * @param int $city_id
     * @return array
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
    public function get(int $city_id): array
    {
        return $this->fetchData('GET', self::OLX_CITIES_URL . '/' . $city_id, [], 'Get OLX city: ' . $city_id);
    }

    /**
     * Get the districts of an OLX city
     * @param int $city_id
     * @return array
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
    public function districts(int $city_id): array
    {
        return $this->fetchData('GET', self::OLX_CITIES_URL . '/' . $city_id . '/districts', [], 'Get OLX city districts: ' . $city_id);
    }
}
