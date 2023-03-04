<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Closure;
use DomainException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use SplQueue;

class MiddlewareDispatcher implements MiddlewareDispatcherInterface
{
    /**
     * @var SplQueue<MiddlewareInterface>|null
     */
    protected ?SplQueue $middleware;

    public function __construct(
        protected RequestHandlerInterface $handler,
        protected ?ContainerInterface $container = null
    ) {
        $this->middleware = new SplQueue();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function add(
        MiddlewareInterface|RequestHandlerInterface|callable|string $middleware
    ): MiddlewareDispatcherInterface {
        if (is_string($middleware)) {
            if ($this->container && $this->container->has($middleware)) {
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

    public function addMiddleware(MiddlewareInterface $middleware): MiddlewareDispatcherInterface
    {
        $this->enqueue($middleware);

        return $this;
    }

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

    public function addCallable(callable $middleware): MiddlewareDispatcherInterface
    {
        if ($this->container && $middleware instanceof Closure) {
            $middleware = $middleware->bindTo($this->container);
        }

        /** @var MiddlewareInterface $middleware */
        $middleware = new class ($middleware) implements MiddlewareInterface {
            /**
             * @var callable
             */
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

    protected function enqueue(MiddlewareInterface $middleware): void
    {
        $this->middleware?->enqueue($middleware);
    }

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

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->process($request, $this->handler);
    }

    public function getMiddleware(): ?SplQueue
    {
        return $this->middleware;
    }
}
