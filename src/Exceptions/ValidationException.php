<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Exceptions;

use Throwable;

class ValidationException extends BaseOlxException
{
    protected array $validation = [];

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null, ?string $title = null, ?string $detail = null, array $validation = [])
    {
        parent::__construct($message, $code, $previous, $title, $detail);
        $this->validation = $validation;
    }

    public function getValidation(): array
    {
        return $this->validation;
    }

}
