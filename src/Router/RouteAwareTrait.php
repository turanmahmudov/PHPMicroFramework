<?php

declare(strict_types=1);

namespace Framework\Router;

use Psr\Http\Server\RequestHandlerInterface;

trait RouteAwareTrait
{
    protected ?RouteCollectorInterface $routeCollector;

    /**
     * @param RequestHandlerInterface|callable|string|array<string, string> $handler
     */
    public function get(string $pattern, RequestHandlerInterface|callable|string|array $handler): RouteInterface
    {
        return $this->getRouteCollector()->map('GET', $pattern, $handler);
    }

    /**
     * @param RequestHandlerInterface|callable|string|array<string, string> $handler
     */
    public function post(string $pattern, RequestHandlerInterface|callable|string|array $handler): RouteInterface
    {
        return $this->getRouteCollector()->map('POST', $pattern, $handler);
    }

    /**
     * @param RequestHandlerInterface|callable|string|array<string, string> $handler
     */
    public function delete(string $pattern, RequestHandlerInterface|callable|string|array $handler): RouteInterface
    {
        return $this->getRouteCollector()->map('DELETE', $pattern, $handler);
    }

    /**
     * @param RequestHandlerInterface|callable|string|array<string, string> $handler
     */
    public function head(string $pattern, RequestHandlerInterface|callable|string|array $handler): RouteInterface
    {
        return $this->getRouteCollector()->map('HEAD', $pattern, $handler);
    }

    /**
     * @param RequestHandlerInterface|callable|string|array<string, string> $handler
     */
    public function options(string $pattern, RequestHandlerInterface|callable|string|array $handler): RouteInterface
    {
        return $this->getRouteCollector()->map('OPTIONS', $pattern, $handler);
    }

    /**
     * @param RequestHandlerInterface|callable|string|array<string, string> $handler
     */
    public function patch(string $pattern, RequestHandlerInterface|callable|string|array $handler): RouteInterface
    {
        return $this->getRouteCollector()->map('PATCH', $pattern, $handler);
    }

    /**
     * @param RequestHandlerInterface|callable|string|array<string, string> $handler
     */
    public function put(string $pattern, RequestHandlerInterface|callable|string|array $handler): RouteInterface
    {
        return $this->getRouteCollector()->map('PUT', $pattern, $handler);
    }

    public function group(string $pattern, callable $callable): GroupInterface
    {
        return $this->getRouteCollector()->group($pattern, $callable);
    }

    public function getRouteCollector(): RouteCollectorInterface
    {
        return $this->routeCollector ??
            $this->routeCollector = (new RouteCollectorFactory())(
                $this->getContainer(),
                $this->responseFactory
            );
    }

    public function setRouteCollector(RouteCollectorInterface $routeCollector): void
    {
        $this->routeCollector = $routeCollector;
    }
}
