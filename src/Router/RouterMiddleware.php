<?php

declare(strict_types=1);

namespace Framework\Router;

use Exception;
use Framework\Http\Exception\MethodNotAllowedException;
use Framework\Http\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class RouterMiddleware implements MiddlewareInterface
{
    /**
     * @var RouteCollectorInterface
     */
    protected RouteCollectorInterface $routeCollector;

    /**
     * @param RouteCollectorInterface $routeCollector
     */
    public function __construct(RouteCollectorInterface $routeCollector)
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
        $routerResults = $this->routeCollector->getRouter()->match($request);
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
                throw new NotFoundException();

            case RouterResults::METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedException();

            default:
                throw new RuntimeException("An unexpected error occurred while performing routing.");
        }
    }
}
