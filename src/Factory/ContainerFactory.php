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
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Laminas\Diactoros\UriFactory;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
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
     * @param array $containerConfig
     * @return ContainerInterface
     * @throws Exception
     */
    public function __invoke(array $containerConfig = []): ContainerInterface
    {
        $defaultConfig = [
            // PSR-17
            ResponseFactoryInterface::class => function () {
                return new ResponseFactory();
            },
            ServerRequestFactoryInterface::class => function () {
                return new ServerRequestFactory();
            },
            StreamFactoryInterface::class => function () {
                return new StreamFactory();
            },
            UriFactoryInterface::class => function () {
                return new UriFactory();
            },
            UploadedFileFactoryInterface::class => function () {
                return new UploadedFileFactory();
            },

            Application::class => factory(ApplicationFactory::class),
            MiddlewareDispatcherInterface::class => factory(MiddlewareDispatcherFactory::class),
            EmitterInterface::class => factory(EmitterFactory::class),
            RouteCollectorInterface::class => factory(RouteCollectorFactory::class),
            RouterInterface::class => factory(RouterFactory::class)
        ];

        $containerConfig = array_merge($defaultConfig, $containerConfig);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->useAnnotations(true);
        $containerBuilder->addDefinitions($containerConfig);

        return $containerBuilder->build();
    }
}
