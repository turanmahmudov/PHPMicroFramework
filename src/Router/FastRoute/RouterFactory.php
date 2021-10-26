<?php

declare(strict_types=1);

namespace Framework\Router\FastRoute;

use Framework\Router\RouterInterface;
use Psr\Container\ContainerInterface;

class RouterFactory
{
    public function __invoke(?ContainerInterface $container): RouterInterface
    {
        $config = $container && $container->has('config') ? $container->get('config') : [];
        $config = $config['router']['fastroute'] ?? [];

        return new Router($config);
    }
}
