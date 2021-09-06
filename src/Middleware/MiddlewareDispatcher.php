<?php

namespace Framework\Middleware;

use Closure;
use DomainException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use SplQueue;

class MiddlewareDispatcher implements MiddlewareDispatcherInterface
{
    /**
     * @var RequestHandlerInterface
     */
    protected RequestHandlerInterface $handler;

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var SplQueue|null
     */
    protected ?SplQueue $middleware;

    public function __construct(RequestHandlerInterface $handler, ContainerInterface $container)
    {
        $this->handler = $handler;
        $this->container = $container;

        $this->middleware = new SplQueue();
    }

    /**
     * {@inheritDoc}
     */
    public function add($middleware)
    {
        if (is_string($middleware)) {
            $middleware = $this->container->get($middleware);
        }

        if ($middleware instanceof MiddlewareInterface) {
            return $this->addMiddleware($middleware);
        }

        if ($middleware instanceof RequestHandlerInterface) {
            return $this->addRequestHandler($middleware);
        }

        if (is_callable($middleware)) {
            return $this->addCallable($middleware);
        }

        throw new RuntimeException(
            'Middleware Type Error'
        );
    }

    /**
     * @param MiddlewareInterface $middleware
     * @return MiddlewareDispatcherInterface
     */
    public function addMiddleware(MiddlewareInterface $middleware): MiddlewareDispatcherInterface
    {
        $this->middleware->enqueue($middleware);

        return $this;
    }

    /**
     * @param RequestHandlerInterface $middleware
     * @return MiddlewareDispatcherInterface
     */
    public function addRequestHandler(RequestHandlerInterface $middleware): MiddlewareDispatcherInterface
    {
        $middleware = new class ($middleware) implements MiddlewareInterface {
            protected RequestHandlerInterface $middleware;

            public function __construct(RequestHandlerInterface $middleware)
            {
                $this->middleware = $middleware;
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return $this->handle($request);
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->middleware->handle($request);
            }
        };

        $this->middleware->enqueue($middleware);

        return $this;
    }

    /**
     * @param callable $middleware
     * @return MiddlewareDispatcherInterface
     */
    public function addCallable(callable $middleware): MiddlewareDispatcherInterface
    {
        if ($this->container && $middleware instanceof Closure) {
            $middleware = $middleware->bindTo($this->container);
        }

        $middleware = new class ($middleware) implements MiddlewareInterface {
            protected $middleware;

            public function __construct(callable $middleware)
            {
                $this->middleware = $middleware;
            }

            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return ($this->middleware)($request, $handler);
            }
        };

        $this->middleware->enqueue($middleware);

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->middleware === null) {
            throw new DomainException(
                'Cannot invoke pipeline handler $handler->handle() more than once'
            );
        }

        if ($this->middleware->isEmpty()) {
            $this->middleware = null;
            return $this->handler->handle($request);
        }

        $middleware = $this->middleware->dequeue();
        $next = clone $this;
        $this->middleware = null;

        return $middleware->process($request, $next);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->process($request, $this->handler);
    }
}