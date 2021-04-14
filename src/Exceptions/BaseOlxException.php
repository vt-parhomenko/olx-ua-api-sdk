<?php
namespace Parhomenko\Olx\Exceptions;


use Throwable;

abstract class BaseOlxException extends \Exception
{
    protected $detail;
    protected $title;

    public function __construct($message = "", $code = 0, Throwable $previous = null, string $title = null, string $detail = null )
    {
        parent::__construct($message, $code, $previous);

        $this->title = $title;
        $this->detail = $detail;
    }

    /**
     * @return string|null
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }
}