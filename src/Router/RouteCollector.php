<?php

namespace Framework\Router;

use Psr\Container\ContainerInterface;
use RuntimeException;

class RouteCollector implements RouteCollectorInterface
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var array<string, RouteInterface>
     */
    protected array $routes = [];

    /**
     * @var string
     */
    protected string $groupPattern = '';

    /**
     * @var array<GroupInterface>
     */
    protected array $routeGroups = [];

    /**
     * @var int
     */
    protected int $routeCounter = 0;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function map(string $method, string $pattern, $handler): RouteInterface
    {
        $pattern = $this->groupPattern . $pattern;

        $route = Route::create(strtoupper($method), $pattern, $handler, $this->routeGroups, $this->container, $this->routeCounter);
        $this->routes[$route->getIdentifier()] = $route;
        $this->routeCounter++;

        return $route;
    }

    /**
     * {@inheritDoc}
     */
    public function group(string $pattern, callable $callback): Group
    {
        $currentGroupPattern = $this->groupPattern;
        $this->groupPattern = $this->groupPattern . $pattern;

        $routeGroup = Group::create($pattern, $callback);
        $this->routeGroups[] = $routeGroup;

        $routeGroup->getRoutes();
        array_pop($this->routeGroups);

        $this->groupPattern = $currentGroupPattern;

        return $routeGroup;
    }

    /**
     * {@inheritDoc}
     */
    public function lookupRoute(string $identifier): RouteInterface
    {
        if (!isset($this->routes[$identifier])) {
            throw new RuntimeException('Route not found.');
        }
        return $this->routes[$identifier];
    }

    /**
     * {@inheritDoc}
     */
    public function getNamedRoute(string $name): RouteInterface
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }

        throw new RuntimeException('Named route does not exist for name: ' . $name);
    }

    /**
     * @return array<string, RouteInterface>
     */
    public function getRoutes() : array
    {
        return $this->routes;
    }

    /**
     * @return RouteMatcherInterface
     */
    public function getRouteMatcher(): RouteMatcherInterface
    {
        return new RouteMatcher($this->routes);
    }
}