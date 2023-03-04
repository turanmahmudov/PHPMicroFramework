<?php

declare(strict_types=1);

namespace Framework\Http;

use Framework\Http\Exception\HttpExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class Exception extends \Exception implements HttpExceptionInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $headers = [];

    /**
     * @var string
     */
    protected $message;

    protected int $status;

    protected ?\Exception $previous;

    /**
     * @param array<string, mixed> $headers
     */
    public function __construct(
        int $status,
        string $message = '',
        \Exception $previous = null,
        array $headers = [],
        int $code = 0
    ) {
        $this->headers = $headers;
        $this->message = $message;
        $this->status = $status;

        parent::__construct($message, $code, $previous);
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function buildJsonResponse(ResponseInterface $response): ResponseInterface
    {
        $this->headers['content-type'] = 'application/json';

        foreach ($this->headers as $key => $value) {
            /** @var ResponseInterface $response */
            $response = $response->withAddedHeader($key, $value);
        }

        if ($response->getBody()->isWritable()) {
            $response->getBody()->write(json_encode([
                'status_code' => $this->status,
                'reason_phrase' => $this->message,
            ]) ?: '');
        }

        return $response->withStatus($this->status, $this->message);
    }
}
