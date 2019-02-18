<?php

namespace Jmj\Parking\Domain\Aggregate\Exception;

use Exception;
use Throwable;

class ExceptionGeneratingUuid extends Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}