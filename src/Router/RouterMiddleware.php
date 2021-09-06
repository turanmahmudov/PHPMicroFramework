<?php

namespace Framework\Router;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterMiddleware implements MiddlewareInterface
{
    /**
     * @var RouteCollector
     */
    protected RouteCollector $routeCollector;

    /**
     * @param RouteCollector $routeCollector
     */
    public function __construct(RouteCollector $routeCollector)
    {
        $this->routeCollector = $routeCollector;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routerResults = $this->routeCollector->getRouteMatcher()->match($request);
        $routeStatus = $routerResults->getRouteStatus();

        $request = $request->withAttribute(RouterResults::class, $routerResults);

        switch ($routeStatus) {
            case RouterResults::FOUND:
                $routeArguments = $routerResults->getRouteArguments();
                $routeIdentifier = $routerResults->getRouteIdentifier() ?? '';

                $route = $this->routeCollector->lookupRoute($routeIdentifier)->withAttributes($routeArguments);
                foreach ($route->getAttributes() as $attribute => $value) {
                    $request = $request->withAttribute($attribute, $value);
                }

                $request = $request->withAttribute(RouteInterface::class, $route);

                return $handler->handle($request);

            case RouterResults::NOT_FOUND:
                throw new Exception("Not Found", 404);

            case RouterResults::METHOD_NOT_ALLOWED:
                throw new Exception("Method Not Allowed", 405);

            default:
                throw new \RuntimeException("An unexpected error occurred while performing routing.");
        }
    }
}