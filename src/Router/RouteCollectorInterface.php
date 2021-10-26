<?php

declare(strict_types=1);

namespace Framework\Router;

use Psr\Http\Server\RequestHandlerInterface;

interface RouteCollectorInterface
{
    /**
     * @param string $method
     * @param string $pattern
     * @param string|callable|array|RequestHandlerInterface $handler
     * @return RouteInterface
     */
    public function map(string $method, string $pattern, $handler): RouteInterface;

    /**
     * @param string $pattern
     * @param callable $callback
     * @return GroupInterface
     */
    public function group(string $pattern, callable $callback): GroupInterface;

    /**
     * @param string $identifier
     * @return RouteInterface
     */
    public function lookupRoute(string $identifier): RouteInterface;

    /**
     * @param string $name
     * @return RouteInterface
     */
    public function getNamedRoute(string $name): RouteInterface;

    /**
     * @return array<string, RouteInterface>
     */
    public function getRoutes(): array;

    /**
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface;
}
