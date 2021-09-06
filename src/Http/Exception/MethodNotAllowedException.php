<?php

namespace Framework\Http\Exception;

use Framework\Http;
use Exception;

class MethodNotAllowedException extends Http\Exception
{
    public function __construct(
        array $allowed = [],
        string $message = 'Method Not Allowed',
        ?Exception $previous = null,
        int $code = 0
    ) {
        $headers = [
            'Allow' => implode(', ', $allowed)
        ];

        parent::__construct(405, $message, $previous, $headers, $code);
    }
}