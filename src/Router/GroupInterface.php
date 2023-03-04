<?php

declare(strict_types=1);

namespace Framework\Router;

use Framework\Middleware\MiddlewareDispatcherInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface GroupInterface
{
    /**
     * @param MiddlewareInterface|RequestHandlerInterface|callable|string|array<string> $middleware
     */
    public function add(MiddlewareInterface|RequestHandlerInterface|callable|string|array $middleware): GroupInterface;

    public function appendMiddlewareToDispatcher(MiddlewareDispatcherInterface $dispatcher): GroupInterface;

    public function getRoutes(): GroupInterface;

    public function getPath(): string;

    /**
     * @return array<MiddlewareInterface|RequestHandlerInterface|callable|string>
     */
    public function getMiddlewares(): array;
}
