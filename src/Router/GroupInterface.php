<?php

declare(strict_types=1);

namespace Framework\Router;

use Framework\Middleware\MiddlewareDispatcherInterface;

interface GroupInterface
{
    /**
     * @param MiddlewareInterface|RequestHandlerInterface|callable|string $middleware
     * @return GroupInterface
     */
    public function add($middleware): GroupInterface;

    /**
     * @param MiddlewareDispatcherInterface $dispatcher
     * @return GroupInterface
     */
    public function appendMiddlewareToDispatcher(MiddlewareDispatcherInterface $dispatcher): GroupInterface;

    /**
     * @return GroupInterface
     */
    public function getRoutes(): GroupInterface;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return array
     */
    public function getMiddlewares(): array;
}
