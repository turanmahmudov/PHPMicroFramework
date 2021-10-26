<?php

declare(strict_types=1);

namespace Framework\Middleware;

use Framework\Application;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

trait MiddlewareAwareTrait
{
    /**
     * @var MiddlewareDispatcherInterface|null
     */
    protected ?MiddlewareDispatcherInterface $middlewareDispatcher;

    /**
     * @param MiddlewareInterface|RequestHandlerInterface|callable|string $middleware
     * @return Application
     */
    public function add($middleware): self
    {
        $this->getMiddlewareDispatcher()->add($middleware);

        return $this;
    }

    /**
     * @return MiddlewareDispatcherInterface
     */
    public function getMiddlewareDispatcher(): MiddlewareDispatcherInterface
    {
        return $this->middlewareDispatcher ??
            $this->middlewareDispatcher = new MiddlewareDispatcher(
                new EmptyPipelineHandler(__CLASS__),
                $this->getContainer()
            );
    }

    /**
     * @param MiddlewareDispatcherInterface $middlewareDispatcher
     */
    public function setMiddlewareDispatcher(MiddlewareDispatcherInterface $middlewareDispatcher): void
    {
        $this->middlewareDispatcher = $middlewareDispatcher;
    }
}
