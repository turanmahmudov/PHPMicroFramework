<?php

declare(strict_types=1);

namespace Framework\Factory;

use Framework\Application;
use Framework\Middleware\MiddlewareDispatcherInterface;
use Framework\Router\RouteCollectorInterface;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class ApplicationFactory
{
    protected static ?ResponseFactoryInterface $responseFactory;

    public function __invoke(
        ?ResponseFactoryInterface $responseFactory = null,
        ?ContainerInterface $container = null,
        ?RouteCollectorInterface $routeCollector = null,
        ?MiddlewareDispatcherInterface $middlewareDispatcher = null,
    ): Application {
        static::$responseFactory = $responseFactory ?? Psr17FactoryDiscovery::findResponseFactory();

        return new Application(
            static::$responseFactory,
            $container,
            $middlewareDispatcher,
            $routeCollector,
        );
    }
}
