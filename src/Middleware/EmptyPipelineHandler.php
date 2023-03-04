<?php

declare(strict_types=1);

namespace Framework\Middleware;

use OutOfBoundsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class EmptyPipelineHandler implements RequestHandlerInterface
{
    public function __construct(
        protected string $className,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new OutOfBoundsException(sprintf(
            '%s cannot handle request; no middleware available to process the request',
            $this->className
        ));
    }
}
