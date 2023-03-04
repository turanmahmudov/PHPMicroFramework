<?php

declare(strict_types=1);

namespace Framework\Router;

use Framework\Middleware\MiddlewareDispatcherInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Group implements GroupInterface
{
    protected string $path;

    /**
     * @var callable
     */
    protected mixed $callable;

    /**
     * @var array<MiddlewareInterface|RequestHandlerInterface|callable|string>
     */
    protected array $middlewares = [];

    protected function __construct(string $path, callable $callable)
    {
        $this->path = $path;
        $this->callable = $callable;
    }

    public static function create(
        string $path,
        callable $callable
    ): self {
        return new self($path, $callable);
    }

    public function getRoutes(): GroupInterface
    {
        $callable = $this->callable;
        $callable();

        return $this;
    }

    public function add(MiddlewareInterface|RequestHandlerInterface|callable|string|array $middleware): GroupInterface
    {
        if (is_array($middleware)) {
            array_push($this->middlewares, ...$middleware);
        } else {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    public function appendMiddlewareToDispatcher(MiddlewareDispatcherInterface $dispatcher): GroupInterface
    {
        foreach ($this->middlewares as $middleware) {
            $dispatcher->add($middleware);
        }

        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
