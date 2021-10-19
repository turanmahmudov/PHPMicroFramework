<?php

namespace Framework\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplQueue;

interface MiddlewareDispatcherInterface extends MiddlewareInterface, RequestHandlerInterface
{
    /**
     * @param MiddlewareInterface|RequestHandlerInterface|callable|string $middleware
     * @return MiddlewareDispatcherInterface
     */
    public function add($middleware): MiddlewareDispatcherInterface;

    /**
     * @return SplQueue
     */
    public function getMiddleware(): SplQueue;
}
