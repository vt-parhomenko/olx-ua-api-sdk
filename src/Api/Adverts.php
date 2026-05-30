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

class Adverts extends AbstractResource
{
    public const OLX_ADVERTS_URL = '/api/partner/adverts';

    /**
     * Get one advert from OLX by ID
     * @param int $id
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
    public function get(int $id): array
    {
        return $this->fetchData('GET', self::OLX_ADVERTS_URL . '/' . $id);
    }

    /**
     * Get all adverts from OLX
     * @param int $offset
     * @param int|null $limit
     * @param string|null $external_id
     * @param string $category_ids
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
    public function getAll(int $offset = 0, ?int $limit = null, ?string $external_id = null, string $category_ids = ''): array
    {
        $params = [
            'offset' => $offset,
            'limit' => $limit,
        ];

        if ($external_id !== null && $external_id !== '') {
            $params['external_id'] = $external_id;
        }
        if ($category_ids !== '') {
            $params['category_ids'] = $category_ids;
        }

        return $this->fetchData('GET', self::OLX_ADVERTS_URL, [ 'query' => $params ]);
    }

    /**
     * Create an offer in OLX
     * @param array $params
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
    public function create(array $params): array
    {
        return $this->fetchData('POST', self::OLX_ADVERTS_URL, [ 'json' => $params ]);
    }

    /**
     * Update an offer in OLX
     * @param int $id
     * @param array $params
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
    public function update(int $id, array $params): array
    {
        return $this->fetchData('PUT', self::OLX_ADVERTS_URL . '/' . $id, [ 'json' => $params ]);
    }

    /**
     * Activate an offer
     * @param int $id
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
    public function activate(int $id): void
    {
        $response = $this->request('POST', self::OLX_ADVERTS_URL . '/' . $id . '/commands', [
            'json' => [ 'command' => 'activate' ],
        ]);

        $this->expectStatus($response);
    }

    /**
     * Deactivate an offer
     * @param int $id
     * @param bool $is_success
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
    public function deactivate(int $id, bool $is_success = true): void
    {
        $response = $this->request('POST', self::OLX_ADVERTS_URL . '/' . $id . '/commands', [
            'json' => [ 'command' => 'deactivate', 'is_success' => $is_success ],
        ]);

        $this->expectStatus($response);
    }

    /**
     * Delete an offer
     * @param int $id
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
    public function delete(int $id): void
    {
        $response = $this->request('DELETE', self::OLX_ADVERTS_URL . '/' . $id);

        $this->expectStatus($response);
    }

    /**
     * Get the statistics of an advert
     * @param int $id
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
    public function statistics(int $id): array
    {
        return $this->fetchData('GET', self::OLX_ADVERTS_URL . '/' . $id . '/statistics', [], 'Get advert statistics: ' . $id);
    }

    /**
     * Delete a single statistic counter of an advert
     * @param int $id
     * @param string $statistic_name
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
    public function deleteStatistic(int $id, string $statistic_name): void
    {
        $response = $this->request('DELETE', self::OLX_ADVERTS_URL . '/' . $id . '/statistics/' . $statistic_name);

        $this->expectStatus($response);
    }

    /**
     * Get the moderation reason for a rejected advert
     * @param int $id
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
    public function moderationReason(int $id): array
    {
        return $this->fetchData('GET', self::OLX_ADVERTS_URL . '/' . $id . '/moderation-reason', [], 'Get advert moderation reason: ' . $id);
    }

    /**
     * Get the logos attached to an advert
     * @param int $id
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
    public function logos(int $id): array
    {
        return $this->fetchData('GET', self::OLX_ADVERTS_URL . '/' . $id . '/logos', [], 'Get advert logos: ' . $id);
    }

    /**
     * Attach a logo to an advert
     * @param int $id
     * @param array $params see the OLX API docs
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
    public function addLogo(int $id, array $params): array
    {
        return $this->fetchData('POST', self::OLX_ADVERTS_URL . '/' . $id . '/logos', [ 'json' => $params ], 'Add advert logo: ' . $id);
    }

    /**
     * Detach a logo from an advert
     * @param int $id
     * @param int $logo_id
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
    public function deleteLogo(int $id, int $logo_id): void
    {
        $response = $this->request('DELETE', self::OLX_ADVERTS_URL . '/' . $id . '/logos/' . $logo_id);

        $this->expectStatus($response);
    }
}
