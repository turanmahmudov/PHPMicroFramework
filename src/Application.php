<?php

declare(strict_types=1);

namespace Framework;

use Framework\Middleware\MiddlewareAwareTrait;
use Framework\Middleware\MiddlewareDispatcherInterface;
use Framework\Router\RouteAwareTrait;
use Framework\Router\RouteCollectorInterface;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Laminas\HttpHandlerRunner\Emitter\EmitterStack;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Application implements MiddlewareInterface, RequestHandlerInterface
{
    use RouteAwareTrait;
    use MiddlewareAwareTrait;

    /**
     * @var ResponseFactoryInterface
     */
    protected ResponseFactoryInterface $responseFactory;

    /**
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container;

    /**
     * @var EmitterInterface|null
     */
    protected ?EmitterInterface $responseEmitter;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ?ContainerInterface $container = null,
        ?MiddlewareDispatcherInterface $middlewareDispatcher = null,
        ?RouteCollectorInterface $routeCollector = null,
        ?EmitterInterface $responseEmitter = null
    ) {
        $this->responseFactory = $responseFactory;
        $this->container = $container;
        $this->middlewareDispatcher = $middlewareDispatcher;
        $this->responseEmitter = $responseEmitter;
        $this->routeCollector = $routeCollector;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getMiddlewareDispatcher()->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->getMiddlewareDispatcher()->process($request, $handler);
    }

    /**
     * @param ServerRequestInterface|null $request
     */
    public function run(?ServerRequestInterface $request = null): void
    {
        if (!$request) {
            if ($this->getContainer()) {
                $request = $this->getContainer()->get(ServerRequestInterface::class);
            } else {
                $request = ServerRequestFactory::fromGlobals();
            }
        }

        $response = $this->handle($request);
        $this->getResponseEmitter()->emit($response);
    }

    /**
     * @return ContainerInterface|null
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return ResponseFactoryInterface
     */
    public function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }

    /**
     * @return EmitterInterface
     */
    public function getResponseEmitter(): EmitterInterface
    {
        return $this->responseEmitter ?? $this->responseEmitter = new SapiEmitter();
    }
}
