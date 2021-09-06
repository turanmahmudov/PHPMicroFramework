<?php

namespace Framework\Http\Exception;

use Framework\Http;
use Exception;

class ForbiddenException extends Http\Exception
{
    public function __construct(string $message = 'Forbidden', ?Exception $previous = null, int $code = 0)
    {
        parent::__construct(403, $message, $previous, [], $code);
    }
}