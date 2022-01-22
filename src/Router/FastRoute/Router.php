<?php

declare(strict_types=1);

namespace Framework\Router\FastRoute;

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\Dispatcher\GroupCountBased as Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as RouteParser;
use Framework\Router\RouteInterface;
use Framework\Router\RouterInterface;
use Framework\Router\RouterResults;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class Router implements RouterInterface
{
    /**
     * @var RouteCollector
     */
    protected RouteCollector $router;

    /**
     * @var Dispatcher
     */
    protected Dispatcher $dispatcher;

    /**
     * @var bool
     */
    protected bool $cacheEnabled = false;

    /**
     * @var string|null
     */
    protected ?string $cacheFile = null;

    /**
     * @var array<RouteInterface>
     */
    protected array $routes = [];

    /**
     * @param array|null $config
     */
    public function __construct(?array $config = null)
    {
        $this->loadConfig($config);

        $this->router = $this->createRouter();
    }

    public function addRoute(RouteInterface $route): void
    {
        $this->routes[] = $route;

        $this->router->addRoute($route->getMethod(), $route->getPath(), $route->getIdentifier());
    }

    /**
     * @inheritDoc
     */
    public function match(ServerRequestInterface $request): RouterResults
    {
        $this->dispatcher = $this->getDispatcher();

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
                $routeInfo[0]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function generateUrl(string $name = "", array $attributes = [], array $queryParams = []): string
    {
        $route = array_filter($this->routes, function ($route) use ($name) {
            return ($route->getName() == $name);
        });

        $route = reset($route);

        if (!$route instanceof RouteInterface) {
            throw new RuntimeException(sprintf(
                'Cannot generate URI for route "%s"; route not found',
                $name
            ));
        }

        $routeParser = new RouteParser();
        $routes = array_reverse($routeParser->parse($route->getPath()));
        $missingParameters = [];

        foreach ($routes as $parts) {
            $missingParameters = $this->missingParameters($parts, $attributes);

            if (!empty($missingParameters)) {
                continue;
            }

            $path = '';
            foreach ($parts as $part) {
                if (is_string($part)) {
                    $path .= $part;
                    continue;
                }

                if (!preg_match('~^' . $part[1] . '$~', (string) $attributes[$part[0]])) {
                    throw new RuntimeException(sprintf(
                        'Parameter value for [%s] did not match the regex `%s`',
                        $part[0],
                        $part[1]
                    ));
                }

                $path .= $attributes[$part[0]];
            }

            return $path;
        }

        throw new RuntimeException(sprintf(
            'Route `%s` expects at least parameter values for [%s], but received [%s]',
            $name,
            implode(',', $missingParameters),
            implode(',', array_keys($attributes))
        ));
    }

    private function missingParameters(array $parts, array $substitutions): array
    {
        $missingParameters = [];

        foreach ($parts as $part) {
            if (is_string($part)) {
                continue;
            }

            $missingParameters[] = $part[0];
        }

        foreach ($missingParameters as $param) {
            if (!isset($substitutions[$param])) {
                return $missingParameters;
            }
        }

        return [];
    }

    protected function loadConfig(?array $config = null): void
    {
        if ($config === null) {
            return;
        }

        if (isset($config['cache_enabled'])) {
            $this->cacheEnabled = (bool) $config['cache_enabled'];
        }

        if (isset($config['cache_file'])) {
            $this->cacheFile = (string) $config['cache_file'];
        }
    }

    /**
     * @param string|null $cacheFile
     * @return Dispatcher
     */
    protected function getDispatcher(?string $cacheFile = null): Dispatcher
    {
        if (null === $cacheFile) {
            return new Dispatcher($this->router->getData());
        }

        if (!file_exists($cacheFile)) {
            file_put_contents(
                $cacheFile,
                '<?php return ' . var_export($this->router->getData(), true) . ';'
            );
        }

        return new Dispatcher(require $cacheFile);
    }

    /**
     * @return RouteCollector
     */
    protected function createRouter(): RouteCollector
    {
        return new RouteCollector(new RouteParser(), new DataGenerator());
    }
}
