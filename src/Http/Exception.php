<?php

namespace Framework\Http;

use Framework\Http\Exception\HttpExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class Exception extends \Exception implements HttpExceptionInterface
{
    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @var string|null
     */
    protected $message;

    /**
     * @var int
     */
    protected int $status;

    /**
     * @var \Exception|null
     */
    protected ?\Exception $previous;

    public function __construct(
        int $status,
        string $message = null,
        \Exception $previous = null,
        array $headers = [],
        int $code = 0
    ) {
        $this->headers = $headers;
        $this->message = $message;
        $this->status = $status;

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * {@inheritDoc}
     */
    public function buildJsonResponse(ResponseInterface $response): ResponseInterface
    {
        $this->headers['content-type'] = 'application/json';

        foreach ($this->headers as $key => $value) {
            /** @var ResponseInterface $response */
            $response = $response->withAddedHeader($key, $value);
        }

        if ($response->getBody()->isWritable()) {
            $response->getBody()->write(json_encode([
                'status_code'   => $this->status,
                'reason_phrase' => $this->message
            ]));
        }

        return $response->withStatus($this->status, $this->message);
    }
}