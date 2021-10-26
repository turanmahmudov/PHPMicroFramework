<?php

declare(strict_types=1);

namespace Framework\Router;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    /**
     * @param RouteInterface $route
     */
    public function addRoute(RouteInterface $route): void;

    /**
     * @param ServerRequestInterface $request
     * @return RouterResults
     */
    public function match(ServerRequestInterface $request): RouterResults;

    /**
     * @param string $name
     * @param array<string, string> $attributes
     * @param array<string, mixed> $queryParams
     * @return string
     */
    public function generateUrl(string $name = "", array $attributes = [], array $queryParams = []): string;
}
