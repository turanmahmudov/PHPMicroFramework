<?php

declare(strict_types=1);

namespace Framework\Http\Exception;

use Psr\Http\Message\ResponseInterface;

interface HttpExceptionInterface
{
    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function buildJsonResponse(ResponseInterface $response): ResponseInterface;

    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array;

    /**
     * @return int
     */
    public function getStatusCode(): int;
}
