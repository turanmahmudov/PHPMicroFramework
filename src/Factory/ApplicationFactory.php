<?php

declare(strict_types=1);

namespace Framework\Factory;

use Framework\Application;
use Framework\Middleware\MiddlewareDispatcherInterface;
use Framework\Router\RouteCollector;
use Laminas\Diactoros\ResponseFactory;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class ApplicationFactory
{
    /**
     * @var ResponseFactoryInterface|null
     */
    protected static ?ResponseFactoryInterface $responseFactory;

    /**
     * @param ResponseFactoryInterface|null $responseFactory
     * @param ContainerInterface|null $container
     * @param RouteCollector|null $routeCollector
     * @param MiddlewareDispatcherInterface|null $middlewareDispatcher
     * @param EmitterInterface|null $responseEmitter
     * @return Application
     */
    public function __invoke(
        ?ResponseFactoryInterface $responseFactory = null,
        ?ContainerInterface $container = null,
        ?RouteCollector $routeCollector = null,
        ?MiddlewareDispatcherInterface $middlewareDispatcher = null,
        ?EmitterInterface $responseEmitter = null
    ): Application {
        static::$responseFactory = $responseFactory ?? new ResponseFactory();

        return new Application(
            static::$responseFactory,
            $container,
            $middlewareDispatcher,
            $routeCollector,
            $responseEmitter
        );
    }
}
