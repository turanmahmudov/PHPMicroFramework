<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplQueue;

interface MiddlewareDispatcherInterface extends MiddlewareInterface, RequestHandlerInterface
{
    public function add(
        MiddlewareInterface|RequestHandlerInterface|callable|string $middleware
    ): MiddlewareDispatcherInterface;

    /**
     * @return SplQueue<MiddlewareInterface>|null
     */
    public function getMiddleware(): ?SplQueue;
}
