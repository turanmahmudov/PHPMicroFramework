<?php

namespace Framework\Middleware;

use OutOfBoundsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class EmptyPipelineHandler implements RequestHandlerInterface
{
    /**
     * @var string
     */
    protected string $className;

    /**
     * EmptyPipelineHandler constructor.
     * @param string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new OutOfBoundsException(sprintf(
            '%s cannot handle request; no middleware available to process the request',
            $this->className
        ));
    }
}