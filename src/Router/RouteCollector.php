<?php

declare(strict_types=1);

namespace Framework\Router;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class RouteCollector implements RouteCollectorInterface
{
    /**
     * @var array<string, RouteInterface>
     */
    protected array $routes = [];

    protected string $groupPattern = '';

    /**
     * @var array<GroupInterface>
     */
    protected array $routeGroups = [];

    protected int $routeCounter = 0;

    /**
     * @var array<string, string>
     */
    protected array $patternMatchers = [
        '/{(.+?):number}/'        => '{$1:[0-9]+}',
        '/{(.+?):word}/'          => '{$1:[a-zA-Z]+}',
        '/{(.+?):alphanum_dash}/' => '{$1:[a-zA-Z0-9-_]+}',
        '/{(.+?):slug}/'          => '{$1:[a-z0-9-]+}',
        '/{(.+?):uuid}/'          => '{$1:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}+}'
    ];

    public function __construct(
        protected ?ContainerInterface $container,
        protected ResponseFactoryInterface $responseFactory,
        protected RouterInterface $router
    ) {
    }

    public function map(
        string $method,
        string $pattern,
        RequestHandlerInterface|callable|string|array $handler
    ): RouteInterface {
        $pattern = $this->parseRoutePath($this->groupPattern . $pattern);

        $route = Route::create(
            strtoupper($method),
            $pattern,
            $handler,
            $this->responseFactory,
            $this->routeGroups,
            $this->container,
            $this->routeCounter
        );
        $this->routes[$route->getIdentifier()] = $route;
        $this->routeCounter++;

        $this->router->addRoute($route);

        return $route;
    }

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

    public function addPatternMatcher(string $alias, string $regex): RouteCollectorInterface
    {
        $pattern = '/{(.+?):' . $alias . '}/';
        $regex = '{$1:' . $regex . '}';
        $this->patternMatchers[$pattern] = $regex;
        return $this;
    }

    public function lookupRoute(string $identifier): RouteInterface
    {
        if (!isset($this->routes[$identifier])) {
            throw new RuntimeException('Route not found.');
        }
        return $this->routes[$identifier];
    }

    public function getNamedRoute(string $name): RouteInterface
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route;
            }
        }

        throw new RuntimeException('Named route does not exist for name: ' . $name);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    protected function parseRoutePath(string $path): string
    {
        return preg_replace(array_keys($this->patternMatchers), array_values($this->patternMatchers), $path) ?: $path;
    }

    public function getRouter(): RouterInterface
    {
        return $this->router;
    }
}
