<?php

namespace Jmj\Parking\Common\Exception;

use Exception;

class ExceptionGeneratingUuid extends Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}