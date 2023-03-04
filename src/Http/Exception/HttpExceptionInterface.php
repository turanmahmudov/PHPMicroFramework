<?php

declare(strict_types=1);

namespace Framework\Http\Exception;

use Psr\Http\Message\ResponseInterface;

interface HttpExceptionInterface
{
    public function buildJsonResponse(ResponseInterface $response): ResponseInterface;

    /**
     * @return array<string, mixed>
     */
    public function getHeaders(): array;

    public function getStatusCode(): int;
}
