<?php

namespace Framework\Router;

use Framework\Middleware\MiddlewareDispatcherInterface;

interface GroupInterface
{
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
     * @return array<mixed>
     */
    public function getMiddlewares(): array;
}
