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

class Threads extends AbstractResource
{
    public const OLX_THREADS_URL = '/api/partner/threads';

    /**
     * Get OLX thread info
     * @param int $thread_id
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
    public function get(int $thread_id): array
    {
        return $this->fetchData('GET', self::OLX_THREADS_URL . '/' . $thread_id, [], 'Get OLX thread: ' . $thread_id);
    }

    /**
     * Get all threads from OLX
     * @param int $offset
     * @param int|null $limit
     * @param int|null $advert_id
     * @param int|null $interlocutor_id
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
    public function getAll(int $offset = 0, ?int $limit = null, ?int $advert_id = null, ?int $interlocutor_id = null): array
    {
        $params = [
            'offset' => $offset,
            'limit' => $limit,
        ];

        if ($advert_id) {
            $params['advert_id'] = $advert_id;
        }
        if ($interlocutor_id) {
            $params['interlocutor_id'] = $interlocutor_id;
        }

        return $this->fetchData('GET', self::OLX_THREADS_URL, [ 'query' => $params ], 'Get all OLX threads');
    }

    /**
     * Mark a thread as read
     * @param int $thread_id
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
    public function markAsRead(int $thread_id): void
    {
        $response = $this->request('POST', self::OLX_THREADS_URL . '/' . $thread_id . '/commands', [
            'json' => [ 'command' => 'mark-as-read' ],
        ]);

        $this->expectStatus($response);
    }

    /**
     * Mark a thread as favourite
     * @param int $thread_id
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
    public function setFavourite(int $thread_id): void
    {
        $response = $this->request('POST', self::OLX_THREADS_URL . '/' . $thread_id . '/commands', [
            'json' => [ 'command' => 'set-favourite', 'set-favourite' => true ],
        ]);

        $this->expectStatus($response);
    }

    /**
     * Remove the favourite mark from a thread
     * @param int $thread_id
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
    public function unsetFavourite(int $thread_id): void
    {
        $response = $this->request('POST', self::OLX_THREADS_URL . '/' . $thread_id . '/commands', [
            'json' => [ 'command' => 'set-favourite', 'set-favourite' => false ],
        ]);

        $this->expectStatus($response);
    }

    /**
     * Post a message to a thread
     * @param int $thread_id
     * @param string $text
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
    public function post(int $thread_id, string $text): void
    {
        $response = $this->request('POST', self::OLX_THREADS_URL . '/' . $thread_id . '/messages', [
            'json' => [ 'text' => $text ],
        ]);

        $this->expectStatus($response, 200);
    }
}
