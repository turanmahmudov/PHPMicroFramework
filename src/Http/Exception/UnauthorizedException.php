<?php

namespace Framework\Http\Exception;

use Framework\Http;
use Exception;

class UnauthorizedException extends Http\Exception
{
    public function __construct(string $message = 'Unauthorized', ?Exception $previous = null, int $code = 0)
    {
        parent::__construct(401, $message, $previous, [], $code);
    }
}
