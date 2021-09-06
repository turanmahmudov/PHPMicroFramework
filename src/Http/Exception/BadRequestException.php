<?php

namespace Framework\Http\Exception;

use Framework\Http;
use Exception;

class BadRequestException extends Http\Exception
{
    public function __construct(string $message = 'Bad Request', ?Exception $previous = null, int $code = 0)
    {
        parent::__construct(400, $message, $previous, [], $code);
    }
}