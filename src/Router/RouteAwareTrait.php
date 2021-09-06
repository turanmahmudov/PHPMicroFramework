<?php

namespace Framework\Router;

use Psr\Http\Server\RequestHandlerInterface;

trait RouteAwareTrait
{
    /**
     * @var RouteCollectorInterface|null
     */
    protected ?RouteCollectorInterface $routeCollector;

    /**
     * @param string $pattern
     * @param string|callable|array<mixed>|RequestHandlerInterface $handler
     * @return RouteInterface
     */
    public function get(string $pattern, $handler): RouteInterface
    {
        return $this->getRouteCollector()->map('GET', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param string|callable|array<mixed>|RequestHandlerInterface $handler
     * @return RouteInterface
     */
    public function post(string $pattern, $handler): RouteInterface
    {
        return $this->getRouteCollector()->map('POST', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param string|callable|array<mixed>|RequestHandlerInterface $handler
     * @return RouteInterface
     */
    public function delete(string $pattern, $handler): RouteInterface
    {
        return $this->getRouteCollector()->map('DELETE', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param string|callable|array<mixed>|RequestHandlerInterface $handler
     * @return RouteInterface
     */
    public function head(string $pattern, $handler): RouteInterface
    {
        return $this->getRouteCollector()->map('HEAD', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param string|callable|array<mixed>|RequestHandlerInterface $handler
     * @return RouteInterface
     */
    public function options(string $pattern, $handler): RouteInterface
    {
        return $this->getRouteCollector()->map('OPTIONS', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param string|callable|array<mixed>|RequestHandlerInterface $handler
     * @return RouteInterface
     */
    public function patch(string $pattern, $handler): RouteInterface
    {
        return $this->getRouteCollector()->map('PATCH', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param string|callable|array<mixed>|RequestHandlerInterface $handler
     * @return RouteInterface
     */
    public function put(string $pattern, $handler): RouteInterface
    {
        return $this->getRouteCollector()->map('PUT', $pattern, $handler);
    }

    /**
     * @param string $pattern
     * @param callable $callable
     * @return GroupInterface
     */
    public function group(string $pattern, callable $callable): GroupInterface
    {
        return $this->getRouteCollector()->group($pattern, $callable);
    }

    /**
     * @return RouteCollectorInterface
     */
    public function getRouteCollector(): RouteCollectorInterface
    {
        return $this->routeCollector;
    }

    /**
     * @param RouteCollectorInterface $routeCollector
     */
    public function setRouteCollector(RouteCollectorInterface $routeCollector): void
    {
        $this->routeCollector = $routeCollector;
    }
}