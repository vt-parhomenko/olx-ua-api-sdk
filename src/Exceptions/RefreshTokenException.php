<?php
namespace Parhomenko\Olx\Exceptions;

use Throwable;

class RefreshTokenException extends BaseOlxException
{
    private $error;
    private $error_description;

    public function __construct($message = "", $code = 0, Throwable $previous = null, $error = null, $error_description = null)
    {
        parent::__construct($message, $code, $previous);
        $this->error = $error;
        $this->error_description = $error_description;
    }

}