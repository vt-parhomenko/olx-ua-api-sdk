<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Exceptions;

use Throwable;

class RefreshTokenException extends BaseOlxException
{
    private mixed $error;
    private mixed $error_description;

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null, mixed $error = null, mixed $error_description = null)
    {
        parent::__construct($message, $code, $previous);
        $this->error = $error;
        $this->error_description = $error_description;
    }

    public function getError(): mixed
    {
        return $this->error;
    }

    public function getErrorDescription(): mixed
    {
        return $this->error_description;
    }

}
