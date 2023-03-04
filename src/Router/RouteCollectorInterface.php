<?php

declare(strict_types=1);

namespace Framework\Router;

use Psr\Http\Server\RequestHandlerInterface;

interface RouteCollectorInterface
{
    /**
     * @param RequestHandlerInterface|callable|string|array<string, string> $handler
     */
    public function map(
        string $method,
        string $pattern,
        RequestHandlerInterface|callable|string|array $handler
    ): RouteInterface;

    public function group(string $pattern, callable $callback): GroupInterface;

    public function lookupRoute(string $identifier): RouteInterface;

    public function getNamedRoute(string $name): RouteInterface;

    /**
     * @return array<string, RouteInterface>
     */
    public function getRoutes(): array;

    public function getRouter(): RouterInterface;
}
