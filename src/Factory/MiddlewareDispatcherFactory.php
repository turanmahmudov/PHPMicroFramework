<?php

namespace Framework\Factory;

use Framework\Middleware\EmptyPipelineHandler;
use Framework\Middleware\MiddlewareDispatcher;
use Framework\Middleware\MiddlewareDispatcherInterface;
use Psr\Container\ContainerInterface;

class MiddlewareDispatcherFactory
{
    /**
     * @param ContainerInterface $container
     * @return MiddlewareDispatcherInterface
     */
    public function __invoke(ContainerInterface $container)
    {
        return new MiddlewareDispatcher(new EmptyPipelineHandler(__CLASS__), $container);
    }
}