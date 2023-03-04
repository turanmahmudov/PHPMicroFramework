<?php

declare(strict_types=1);

namespace Framework\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

interface RouteInterface extends RequestHandlerInterface
{
    public function run(ServerRequestInterface $request): ResponseInterface;

    /**
     * @param MiddlewareInterface|RequestHandlerInterface|callable|string|array<string> $middleware
     */
    public function add(MiddlewareInterface|RequestHandlerInterface|callable|string|array $middleware): RouteInterface;

    public function name(string $name): RouteInterface;

    public function getName(): string;

    public function getMethod(): string;

    public function getPath(): string;

    /**
     * @return RequestHandlerInterface|callable|string|array<string, string>
     */
    public function getRequestHandler(): RequestHandlerInterface|callable|string|array;

    /**
     * @param array<string, mixed> $attributes
     */
    public function withAttributes(array $attributes): RouteInterface;

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array;

    public function getIdentifier(): string;
}
