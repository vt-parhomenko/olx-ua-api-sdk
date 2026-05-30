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

class Users extends AbstractResource
{
    public const OLX_AUTHENTICATED_USER_URL = '/api/partner/users/me';
    public const OLX_USER_URL = '/api/partner/users';
    public const OLX_ACCOUNT_BALANCE_URL = '/api/partner/users/me/account-balance';
    public const OLX_PAYMENT_METHODS_URL = '/api/partner/users/me/payment-methods';
    public const OLX_BILLING_URL = '/api/partner/users/me/billing';
    public const OLX_PREPAID_INVOICES_URL = '/api/partner/users/me/prepaid-invoices';
    public const OLX_POSTPAID_INVOICES_URL = '/api/partner/users/me/postpaid-invoices';

    /**
     * Get information about the authenticated user
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
    public function me(): array
    {
        return $this->fetchData('GET', self::OLX_AUTHENTICATED_USER_URL, [], 'Get authenticated user');
    }

    /**
     * Get one user from OLX by ID
     * @param int $user_id
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
    public function get(int $user_id): array
    {
        return $this->fetchData('GET', self::OLX_USER_URL . '/' . $user_id, [], 'Get OLX user: ' . $user_id);
    }

    /**
     * Get the authenticated user's account balance (wallet, bonus, refund).
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
    public function accountBalance(): array
    {
        return $this->fetchData('GET', self::OLX_ACCOUNT_BALANCE_URL, [], 'Get account balance');
    }

    /**
     * Get the authenticated user's available payment methods (account, postpaid).
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
    public function paymentMethods(): array
    {
        return $this->fetchData('GET', self::OLX_PAYMENT_METHODS_URL, [], 'Get payment methods');
    }

    /**
     * Get the authenticated user's billing entries.
     * @param int $page
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
    public function billing(int $page = 1, ?int $limit = null): array
    {
        return $this->fetchData('GET', self::OLX_BILLING_URL, [
            'query' => [ 'page' => $page, 'limit' => $limit ],
        ], 'Get billing');
    }

    /**
     * Get the authenticated user's prepaid invoices.
     * @param int $page
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
    public function prepaidInvoices(int $page = 1, ?int $limit = null): array
    {
        return $this->fetchData('GET', self::OLX_PREPAID_INVOICES_URL, [
            'query' => [ 'page' => $page, 'limit' => $limit ],
        ], 'Get prepaid invoices');
    }

    /**
     * Get the authenticated user's postpaid invoices.
     * @param int $page
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
    public function postpaidInvoices(int $page = 1, ?int $limit = null): array
    {
        return $this->fetchData('GET', self::OLX_POSTPAID_INVOICES_URL, [
            'query' => [ 'page' => $page, 'limit' => $limit ],
        ], 'Get postpaid invoices');
    }
}
