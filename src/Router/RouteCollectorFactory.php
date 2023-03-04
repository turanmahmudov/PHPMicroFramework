<?php

declare(strict_types=1);

namespace Framework\Router;

use Framework\Router\FastRoute\RouterFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;

class RouteCollectorFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
        ?ContainerInterface $container,
        ResponseFactoryInterface $responseFactory
    ): RouteCollectorInterface {
        if ($container && $container->has(RouterInterface::class)) {
            return new RouteCollector($container, $responseFactory, $container->get(RouterInterface::class));
        }

        return new RouteCollector($container, $responseFactory, (new RouterFactory())($container));
    }
}
