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
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container;

    /**
     * @var SplQueue<MiddlewareInterface>|null
     */
    protected ?SplQueue $middleware;

    /**
     * MiddlewareDispatcher constructor.
     * @param RequestHandlerInterface $handler
     * @param ContainerInterface|null $container
     */
    public function __construct(RequestHandlerInterface $handler, ?ContainerInterface $container = null)
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
            if ($this->container) {
                $middleware = $this->container->get($middleware);
            } else {
                $middleware = new $middleware();
            }
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
        $this->enqueue($middleware);

        return $this;
    }

    /**
     * @param RequestHandlerInterface $middleware
     * @return MiddlewareDispatcherInterface
     */
    public function addRequestHandler(RequestHandlerInterface $middleware): MiddlewareDispatcherInterface
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = new class ($middleware) implements MiddlewareInterface {
            protected RequestHandlerInterface $middleware;

            public function __construct(RequestHandlerInterface $middleware)
            {
                $this->middleware = $middleware;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return $this->handle($request);
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->middleware->handle($request);
            }
        };

        $this->enqueue($middleware);

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

        /** @var MiddlewareInterface $middleware */
        $middleware = new class ($middleware) implements MiddlewareInterface {
            protected $middleware;

            public function __construct(callable $middleware)
            {
                $this->middleware = $middleware;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                return ($this->middleware)($request, $handler);
            }
        };

        $this->enqueue($middleware);

        return $this;
    }

    /**
     * @param MiddlewareInterface $middleware
     */
    protected function enqueue(MiddlewareInterface $middleware): void
    {
        if ($this->middleware) {
            $this->middleware->enqueue($middleware);
        }
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
