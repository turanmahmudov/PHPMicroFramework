<?php

declare(strict_types=1);

namespace Framework\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DispatchMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var RouteInterface $routeResult */
        $routeResult = $request->getAttribute(RouteInterface::class);

        if (!$routeResult instanceof RouteInterface) {
            return $handler->handle($request);
        }

        return $routeResult->run($request);
    }
}
