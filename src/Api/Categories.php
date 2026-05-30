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

class Categories extends AbstractResource
{
    public const OLX_CATEGORIES_URL = '/api/partner/categories';

    /**
     * Get one OLX category by ID
     * @param int $category_id
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
    public function get(int $category_id): array
    {
        return $this->fetchData('GET', self::OLX_CATEGORIES_URL . '/' . $category_id, [], 'Get OLX category: ' . $category_id);
    }

    /**
     * Get all OLX categories, optionally limited to a parent category
     * @param int $parent_id
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
    public function getAll(int $parent_id = 0): array
    {
        $query = $parent_id ? [ 'parent_id' => $parent_id ] : [];

        return $this->fetchData('GET', self::OLX_CATEGORIES_URL, [ 'query' => $query ], 'Get all OLX categories, parent_id: ' . $parent_id);
    }

    /**
     * Get the attributes of an OLX category
     * @param int $category_id
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
    public function attributes(int $category_id): array
    {
        return $this->fetchData('GET', self::OLX_CATEGORIES_URL . '/' . $category_id . '/attributes', [], 'Get OLX category attributes: ' . $category_id);
    }

    /**
     * Suggest the most relevant categories for an advert title.
     * @param string $q the advert title (min. 3 characters)
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
    public function suggestion(string $q): array
    {
        return $this->fetchData('GET', self::OLX_CATEGORIES_URL . '/suggestion', [ 'query' => [ 'q' => $q ] ], 'Get category suggestion: ' . $q);
    }
}
