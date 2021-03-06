<?php

declare(strict_types=1);

namespace Framework\Router;

use Framework\Middleware\MiddlewareDispatcherInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Group implements GroupInterface
{
    /**
     * @var string
     */
    protected string $path;

    /**
     * @var callable
     */
    protected $callable;

    /**
     * @var array
     */
    protected array $middlewares = [];

    /**
     * @param string $path
     * @param callable $callable
     */
    protected function __construct(string $path, callable $callable)
    {
        $this->path = $path;
        $this->callable = $callable;
    }

    /**
     * @param string $path
     * @param callable $callable
     * @return Group
     */
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

    /**
     * @param MiddlewareInterface|RequestHandlerInterface|callable|string $middleware
     * @return GroupInterface
     */
    public function add($middleware): GroupInterface
    {
        if (is_array($middleware)) {
            array_push($this->middlewares, ...$middleware);
        } else {
            array_push($this->middlewares, $middleware);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function appendMiddlewareToDispatcher(MiddlewareDispatcherInterface $dispatcher): GroupInterface
    {
        foreach ($this->middlewares as $middleware) {
            $dispatcher->add($middleware);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
