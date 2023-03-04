<?php

declare(strict_types=1);

namespace Framework\Factory;

use DI\ContainerBuilder;
use Exception;
use Framework\Application;
use Framework\Middleware\MiddlewareDispatcherInterface;
use Framework\Router\FastRoute\RouterFactory;
use Framework\Router\RouteCollectorInterface;
use Framework\Router\RouteCollectorFactory;
use Framework\Router\RouterInterface;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

use function DI\factory;

class ContainerFactory
{
    /**
     * @param array<string, mixed> $containerConfig
     * @throws Exception
     */
    public function __invoke(array $containerConfig = []): ContainerInterface
    {
        $defaultConfig = [
            // PSR-17
            ResponseFactoryInterface::class => function () {
                return Psr17FactoryDiscovery::findResponseFactory();
            },
            ServerRequestFactoryInterface::class => function () {
                return Psr17FactoryDiscovery::findServerRequestFactory();
            },
            StreamFactoryInterface::class => function () {
                return Psr17FactoryDiscovery::findStreamFactory();
            },
            UriFactoryInterface::class => function () {
                return Psr17FactoryDiscovery::findUriFactory();
            },
            UploadedFileFactoryInterface::class => function () {
                return Psr17FactoryDiscovery::findUploadedFileFactory();
            },

            Application::class => factory(ApplicationFactory::class),
            MiddlewareDispatcherInterface::class => factory(MiddlewareDispatcherFactory::class),
            RouteCollectorInterface::class => factory(RouteCollectorFactory::class),
            RouterInterface::class => factory(RouterFactory::class)
        ];

        $containerConfig = array_merge($defaultConfig, $containerConfig);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->addDefinitions($containerConfig);

        return $containerBuilder->build();
    }
}
