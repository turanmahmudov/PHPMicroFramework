<?php

declare(strict_types=1);

namespace Framework\Router;

class RouterResults
{
    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;

    protected string $method;

    protected string $path;

    protected int $routeStatus;

    protected ?string $routeIdentifier;

    /**
     * @var array<string, mixed>
     */
    protected array $routeArguments;

    /**
     * @var array<string, mixed>
     */
    protected array $allowedMethods;

    /**
     * @param array<string, mixed> $routeArguments
     * @param array<string, mixed> $allowedMethods
     */
    public function __construct(
        string $method,
        string $path,
        int $routeStatus,
        ?string $routeIdentifier = null,
        array $routeArguments = [],
        array $allowedMethods = []
    ) {
        $this->method = $method;
        $this->path = $path;
        $this->routeStatus = $routeStatus;
        $this->routeIdentifier = $routeIdentifier;
        $this->routeArguments = $routeArguments;
        $this->allowedMethods = $allowedMethods;
    }

    public function getRouteStatus(): int
    {
        return $this->routeStatus;
    }

    public function getRouteIdentifier(): ?string
    {
        return $this->routeIdentifier;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRouteArguments(): array
    {
        return $this->routeArguments;
    }
}
