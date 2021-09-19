<?php

namespace Framework\Router;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use RuntimeException;

class RouteCollector implements RouteCollectorInterface
{
    /**
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container;

    /**
     * @var ResponseFactoryInterface
     */
    protected ResponseFactoryInterface $responseFactory;

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
     * @var array<string, string>
     */
    protected array $patternMatchers = [
        '/{(.+?):number}/'        => '{$1:[0-9]+}',
        '/{(.+?):word}/'          => '{$1:[a-zA-Z]+}',
        '/{(.+?):alphanum_dash}/' => '{$1:[a-zA-Z0-9-_]+}',
        '/{(.+?):slug}/'          => '{$1:[a-z0-9-]+}',
        '/{(.+?):uuid}/'          => '{$1:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}+}'
    ];

    /**
     * @param ContainerInterface|null $container
     * @param ResponseFactoryInterface $responseFactory
     */
    public function __construct(?ContainerInterface $container, ResponseFactoryInterface $responseFactory)
    {
        $this->container = $container;
        $this->responseFactory = $responseFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function map(string $method, string $pattern, $handler): RouteInterface
    {
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
     * @param string $alias
     * @param string $regex
     * @return RouteCollectorInterface
     */
    public function addPatternMatcher(string $alias, string $regex): RouteCollectorInterface
    {
        $pattern = '/{(.+?):' . $alias . '}/';
        $regex = '{$1:' . $regex . '}';
        $this->patternMatchers[$pattern] = $regex;
        return $this;
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
    public function getRoutes(): array
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

    /**
     * @param string $path
     * @return string
     */
    protected function parseRoutePath(string $path): string
    {
        return preg_replace(array_keys($this->patternMatchers), array_values($this->patternMatchers), $path);
    }
}
