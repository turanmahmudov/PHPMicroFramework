<?php

declare(strict_types=1);

namespace Framework\Http\Exception;

use Framework\Http;
use Exception;

class MethodNotAllowedException extends Http\Exception
{
    /**
     * MethodNotAllowedException constructor.
     * @param array<string, mixed> $allowed
     * @param string $message
     * @param Exception|null $previous
     * @param int $code
     */
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
