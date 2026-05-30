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

class Messages extends AbstractResource
{
    /**
     * Get all messages of a thread from OLX
     * @param int $thread_id
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
    public function get(int $thread_id, int $offset = 0, ?int $limit = null): array
    {
        $apiurl = Threads::OLX_THREADS_URL . '/' . $thread_id . '/messages';

        return $this->fetchData('GET', $apiurl, [
            'query' => [ 'offset' => $offset, 'limit' => $limit ],
        ], 'Get all OLX thread messages');
    }

    /**
     * Get a single message of a thread from OLX
     * @param int $thread_id
     * @param int $message_id
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
    public function getOne(int $thread_id, int $message_id): array
    {
        $apiurl = Threads::OLX_THREADS_URL . '/' . $thread_id . '/messages/' . $message_id;

        return $this->fetchData('GET', $apiurl, [], 'Get OLX thread message: ' . $message_id);
    }
}
