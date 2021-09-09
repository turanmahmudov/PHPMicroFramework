<?php

namespace Framework\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

interface RouteInterface extends RequestHandlerInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function run(ServerRequestInterface $request): ResponseInterface;

    /**
     * @param string $name
     * @return RouteInterface
     */
    public function name(string $name): RouteInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @return string
     */
    public function getPath(): string;

    /**
     * @return mixed
     */
    public function getRequestHandler();

    /**
     * @param array<string, mixed> $attributes
     * @return RouteInterface
     */
    public function withAttributes(array $attributes): RouteInterface;

    /**
     * @return array<string, mixed>
     */
    public function getAttributes(): array;

    /**
     * @return string
     */
    public function getIdentifier(): string;
}