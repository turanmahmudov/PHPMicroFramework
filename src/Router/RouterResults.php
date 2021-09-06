<?php

namespace Framework\Router;

class RouterResults
{
    public const NOT_FOUND = 0;
    public const FOUND = 1;
    public const METHOD_NOT_ALLOWED = 2;

    /**
     * @var string
     */
    protected string $method;

    /**
     * @var string
     */
    protected string $path;

    /**
     * @var int
     */
    protected int $routeStatus;

    /**
     * @var string|null
     */
    protected ?string $routeIdentifier;

    /**
     * @var array<string, mixed>
     */
    protected array $routeArguments;

    /**
     * RouterResults constructor.
     * @param string $method
     * @param string $path
     * @param int $routeStatus
     * @param string|null $routeIdentifier
     * @param array<string, mixed> $routeArguments
     */
    public function __construct(
        string $method,
        string $path,
        int $routeStatus,
        ?string $routeIdentifier = null,
        array $routeArguments = []
    ) {
        $this->method = $method;
        $this->path = $path;
        $this->routeStatus = $routeStatus;
        $this->routeIdentifier = $routeIdentifier;
        $this->routeArguments = $routeArguments;
    }

    /**
     * @return int
     */
    public function getRouteStatus(): int
    {
        return $this->routeStatus;
    }

    /**
     * @return string|null
     */
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