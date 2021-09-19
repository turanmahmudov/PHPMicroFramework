<?php

namespace Framework\Http\Exception;

use Framework\Http;
use Exception;

class NotFoundException extends Http\Exception
{
    public function __construct(string $message = 'Not Found', ?Exception $previous = null, int $code = 0)
    {
        parent::__construct(404, $message, $previous, [], $code);
    }
}
