<?php

namespace Framework\Factory;

use DI\ContainerBuilder;
use Exception;
use Framework\Application;
use Framework\Middleware\MiddlewareDispatcherInterface;
use Framework\Router\RouteCollector;
use Framework\Router\RouteCollectorInterface;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Laminas\Diactoros\UriFactory;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use function DI\factory;

class ContainerFactory
{
    /**
     * @param array<mixed, mixed> $containerConfig
     * @return ContainerInterface
     * @throws Exception
     */
    public function __invoke(array $containerConfig = []): ContainerInterface
    {
        $defaultConfig = [
            // PSR-17
            ResponseFactoryInterface::class => function() {
                return new ResponseFactory();
            },
            ServerRequestFactoryInterface::class => function() {
                return new ServerRequestFactory();
            },
            StreamFactoryInterface::class => function() {
                return new StreamFactory();
            },
            UriFactoryInterface::class => function() {
                return new UriFactory();
            },
            UploadedFileFactoryInterface::class => function() {
                return new UploadedFileFactory();
            },

            // PSR-7
            ResponseInterface::class => function(ResponseFactoryInterface $responseFactory) {
                return $responseFactory->createResponse();
            },
            ServerRequestInterface::class => function(ServerRequestFactoryInterface $serverRequestFactory) {
                return $serverRequestFactory::fromGlobals();
            },

            Application::class => factory(ApplicationFactory::class),
            MiddlewareDispatcherInterface::class => factory(MiddlewareDispatcherFactory::class),
            EmitterInterface::class => factory(EmitterFactory::class),
            RouteCollectorInterface::class => function(ContainerInterface $container) {
                return new RouteCollector($container);
            }
        ];

        $containerConfig = array_merge($defaultConfig, $containerConfig);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->useAutowiring(true);
        $containerBuilder->useAnnotations(true);
        $containerBuilder->addDefinitions($containerConfig);

        return $containerBuilder->build();
    }
}