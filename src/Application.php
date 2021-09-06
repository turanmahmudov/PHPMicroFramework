<?php

namespace Framework;

use Framework\Middleware\MiddlewareAwareTrait;
use Framework\Middleware\MiddlewareDispatcherInterface;
use Framework\Router\RouteAwareTrait;
use Framework\Router\RouteCollectorInterface;
use Laminas\HttpHandlerRunner\Emitter\EmitterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Application implements MiddlewareInterface, RequestHandlerInterface
{
    use RouteAwareTrait, MiddlewareAwareTrait;

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
        return $this->middlewareDispatcher->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->middlewareDispatcher->process($request, $handler);
    }

    /**
     * @param ServerRequestInterface|null $request
     */
    public function run(?ServerRequestInterface $request = null): void
    {
        if (!$request) {
            $request = $this->container->get(ServerRequestInterface::class);
        }

        $response = $this->handle($request);
        $this->responseEmitter->emit($response);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
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
}