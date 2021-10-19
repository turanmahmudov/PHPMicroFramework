<?php

namespace Framework\Router;

use FastRoute\Dispatcher\GroupCountBased as Dispatcher;
use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\RouteParser\Std as RouteParser;
use FastRoute\RouteCollector;
use Psr\Http\Message\ServerRequestInterface;

use function file_exists;
use function file_put_contents;
use function rawurldecode;
use function var_export;

final class RouteMatcher implements RouteMatcherInterface
{
    /**
     * @var array<RouteInterface>
     */
    protected array $routes;

    /**
     * @var Dispatcher
     */
    protected Dispatcher $dispatcher;

    /**
     * @param array<string, RouteInterface> $routes
     * @param string|null $cacheFile
     */
    public function __construct(array $routes, ?string $cacheFile = null)
    {
        $this->routes = $routes;
        $this->dispatcher = $this->getDispatcher($cacheFile);
    }

    /**
     * {@inheritDoc}
     */
    public function match(ServerRequestInterface $request): RouterResults
    {
        $method = $request->getMethod();
        $path = rawurldecode($request->getUri()->getPath());

        $routeInfo = $this->dispatcher->dispatch($method, $path);

        if ($routeInfo[0] == $this->getDispatcher()::FOUND) {
            return new RouterResults(
                $method,
                $path,
                $routeInfo[0],
                $routeInfo[1],
                $routeInfo[2] ?? []
            );
        } elseif ($routeInfo[0] == $this->getDispatcher()::METHOD_NOT_ALLOWED) {
            return new RouterResults(
                $method,
                $path,
                $routeInfo[0],
                null,
                [],
                $routeInfo[1]
            );
        } else {
            return new RouterResults(
                $method,
                $path,
                $routeInfo[0],
                null
            );
        }
    }

    /**
     * @param string|null $cacheFile
     * @return Dispatcher
     */
    protected function getDispatcher(?string $cacheFile = null): Dispatcher
    {
        if (null === $cacheFile) {
            return new Dispatcher($this->getRouteCollector()->getData());
        }

        if (!file_exists($cacheFile)) {
            file_put_contents(
                $cacheFile,
                '<?php return ' . var_export($this->getRouteCollector()->getData(), true) . ';'
            );
        }

        return new Dispatcher(require $cacheFile);
    }

    /**
     * @return RouteCollector
     */
    protected function getRouteCollector(): RouteCollector
    {
        $routeCollector = new RouteCollector(new RouteParser(), new DataGenerator());

        foreach ($this->routes as $route) {
            $routeCollector->addRoute($route->getMethod(), $route->getPath(), $route->getIdentifier());
        }

        return $routeCollector;
    }
}
