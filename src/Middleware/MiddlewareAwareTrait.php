<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Application;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

trait MiddlewareAwareTrait
{
    protected ?MiddlewareDispatcherInterface $middlewareDispatcher;

    public function add(MiddlewareInterface|RequestHandlerInterface|callable|string $middleware): Application
    {
        $this->getMiddlewareDispatcher()->add($middleware);

        return $this;
    }

    public function getMiddlewareDispatcher(): MiddlewareDispatcherInterface
    {
        return $this->middlewareDispatcher ??
            $this->middlewareDispatcher = new MiddlewareDispatcher(
                new EmptyPipelineHandler(__CLASS__),
                $this->getContainer()
            );
    }

    public function setMiddlewareDispatcher(MiddlewareDispatcherInterface $middlewareDispatcher): void
    {
        $this->middlewareDispatcher = $middlewareDispatcher;
    }
}
