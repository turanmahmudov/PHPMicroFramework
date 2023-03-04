<?php

declare(strict_types=1);

namespace Framework;

use Framework\Middleware\MiddlewareAwareTrait;
use Framework\Middleware\MiddlewareDispatcherInterface;
use Framework\Router\RouteAwareTrait;
use Framework\Router\RouteCollectorInterface;
use Http\Discovery\Psr17Factory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Application implements MiddlewareInterface, RequestHandlerInterface
{
    use RouteAwareTrait;
    use MiddlewareAwareTrait;

    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
        protected ?ContainerInterface $container = null,
        ?MiddlewareDispatcherInterface $middlewareDispatcher = null,
        ?RouteCollectorInterface $routeCollector = null,
    ) {
        $this->middlewareDispatcher = $middlewareDispatcher;
        $this->routeCollector = $routeCollector;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getMiddlewareDispatcher()->handle($request);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->getMiddlewareDispatcher()->process($request, $handler);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function run(?ServerRequestInterface $request = null): void
    {
        if (!$request) {
            if ($this->getContainer() instanceof ContainerInterface) {
                $request = $this->getContainer()->get(ServerRequestInterface::class);
            } else {
                $psr17Factory = new Psr17Factory();
                $request = $psr17Factory->createServerRequestFromGlobals();
            }
        }

        $response = $this->handle($request);
        $this->getResponseEmitter()->emit($response);
    }

    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    public function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

    public function getResponseEmitter(): ResponseEmitter
    {
        return new ResponseEmitter();
    }
}
