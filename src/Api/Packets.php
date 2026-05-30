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

class Packets extends AbstractResource
{
    public const OLX_PACKETS_URL = '/api/partner/packets';
    public const OLX_ZONES_URL = '/api/partner/zones';
    public const OLX_USER_PACKETS_URL = '/api/partner/users/me/packets';

    /**
     * Get the packets available for a category and payment method.
     * @param int $category_id
     * @param string $payment_method one of: account, postpaid
     * @param string|null $type one of: base, mega, all
     * @param bool|null $with_features
     * @param int|null $zone_id
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
    public function getAll(int $category_id, string $payment_method, ?string $type = null, ?bool $with_features = null, ?int $zone_id = null): array
    {
        $query = [
            'category_id' => $category_id,
            'payment_method' => $payment_method,
        ];

        if ($type !== null) {
            $query['type'] = $type;
        }
        if ($with_features !== null) {
            $query['with_features'] = $with_features ? 1 : 0;
        }
        if ($zone_id !== null) {
            $query['zone_id'] = $zone_id;
        }

        return $this->fetchData('GET', self::OLX_PACKETS_URL, [ 'query' => $query ], 'Get available packets');
    }

    /**
     * Get the geographical zones used for packet pricing in a category.
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
    public function zones(int $category_id): array
    {
        return $this->fetchData('GET', self::OLX_ZONES_URL, [ 'query' => [ 'category_id' => $category_id ] ], 'Get packet zones');
    }

    /**
     * Get the packets owned by the authenticated user.
     * @param int $offset
     * @param int|null $limit
     * @param string|null $availability one of: active, inactive
     * @param string|null $sort_by one of: expired_at, activated_at
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
    public function userPackets(int $offset = 0, ?int $limit = null, ?string $availability = null, ?string $sort_by = null): array
    {
        $query = [ 'offset' => $offset, 'limit' => $limit ];

        if ($availability !== null) {
            $query['availability'] = $availability;
        }
        if ($sort_by !== null) {
            $query['sort_by'] = $sort_by;
        }

        return $this->fetchData('GET', self::OLX_USER_PACKETS_URL, [ 'query' => $query ], 'Get user packets');
    }

    /**
     * Buy a packet for the authenticated user's account.
     * @param array $params see the OLX API docs (category_id, payment_method, size, ...)
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
    public function buyForUser(array $params): array
    {
        return $this->fetchData('POST', self::OLX_USER_PACKETS_URL, [ 'json' => $params ], 'Buy user packet');
    }

    /**
     * Apply (purchase) a packet to an advert.
     * @param int $advert_id
     * @param array $params see the OLX API docs (payment_method, is_premium, ...)
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
    public function buyForAdvert(int $advert_id, array $params): array
    {
        return $this->fetchData('POST', Adverts::OLX_ADVERTS_URL . '/' . $advert_id . '/packets', [ 'json' => $params ], 'Buy packet for advert: ' . $advert_id);
    }
}
