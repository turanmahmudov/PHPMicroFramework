<?php

declare(strict_types=1);

namespace Framework\Factory;

use Framework\Middleware\EmptyPipelineHandler;
use Framework\Middleware\MiddlewareDispatcher;
use Framework\Middleware\MiddlewareDispatcherInterface;
use Psr\Container\ContainerInterface;

class MiddlewareDispatcherFactory
{
    public function __invoke(ContainerInterface $container): MiddlewareDispatcherInterface
    {
        return new MiddlewareDispatcher(new EmptyPipelineHandler(__CLASS__), $container);
    }
}
