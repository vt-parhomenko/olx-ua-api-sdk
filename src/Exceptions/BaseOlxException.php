<?php

declare(strict_types=1);

namespace Parhomenko\Olx\Exceptions;

use Exception;
use Throwable;

abstract class BaseOlxException extends Exception
{
    protected ?string $detail;
    protected ?string $title;

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null, ?string $title = null, ?string $detail = null)
    {
        parent::__construct($message, $code, $previous);

        $this->title = $title;
        $this->detail = $detail;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
}
