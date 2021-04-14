<?php
namespace Parhomenko\Olx\Exceptions;


use Throwable;

class ValidationException extends BaseOlxException
{
    protected $validation;

    public function __construct($message = "", $code = 0, Throwable $previous = null,  string $title = null, string $detail = null, array $validation = [] )
    {
        parent::__construct($message, $code, $previous);
        $this->validation = $validation;
    }

    /**
     * @return array
     */
    public function getValidation() : array
    {
        return $this->validation;
    }

}