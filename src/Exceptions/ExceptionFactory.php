<?php
namespace Parhomenko\Olx\Exceptions;

use Exception;

class ExceptionFactory
{
    /**
     * @param Exception $e
     * @throws BadRequestException
     * @throws CallLimitException
     * @throws ForbiddenException
     * @throws NotAcceptableException
     * @throws NotFoundException
     * @throws ServerException
     * @throws UnauthorizedException
     * @throws UnsupportedMediaTypeException
     */
    public static function throw( Exception  $e ): void
    {
        switch ( $e->getCode() )
        {
            case 400:
                throw new BadRequestException( $e->getMessage(), $e->getCode() );
            case 401:
                throw new UnauthorizedException( $e->getMessage(), $e->getCode() );
            case 403:
                throw new ForbiddenException( $e->getMessage(), $e->getCode() );
            case 404:
                throw new NotFoundException( $e->getMessage(), $e->getCode() );
            case 406:
                throw new NotAcceptableException( $e->getMessage(), $e->getCode() );
            case 415:
                throw new UnsupportedMediaTypeException( $e->getMessage(), $e->getCode() );
            case 429:
                throw new CallLimitException( $e->getMessage(), $e->getCode() );
            case 500:
                throw new ServerException( $e->getMessage(), $e->getCode() );
        }
    }
}