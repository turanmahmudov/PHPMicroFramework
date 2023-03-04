<?php

declare(strict_types=1);

namespace Framework\Router;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    public function addRoute(RouteInterface $route): void;

    public function match(ServerRequestInterface $request): RouterResults;

    /**
     * @param array<string, string> $attributes
     * @param array<string, mixed> $queryParams
     */
    public function generateUrl(string $name = "", array $attributes = [], array $queryParams = []): string;
}
