<?php

declare(strict_types=1);

namespace Framework\Router;

use ArgumentsResolver\InDepthArgumentsResolver;
use Framework\Middleware\MiddlewareDispatcher;
use Framework\Middleware\MiddlewareDispatcherInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;
use RuntimeException;

final class Route implements RouteInterface
{
    protected string $identifier;

    protected string $name = '';

    protected string $method;

    protected string $path;

    /** @var RequestHandlerInterface|callable|string|array<string, string> */
    protected mixed $requestHandler;

    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * @var array<GroupInterface>
     */
    protected array $groups;

    protected MiddlewareDispatcherInterface $middlewareDispatcher;

    protected ?ContainerInterface $container;

    protected ResponseFactoryInterface $responseFactory;

    protected bool $groupMiddlewareAppended = false;

    /**
     * @param RequestHandlerInterface|callable|string|array<string, string> $requestHandler
     * @param array<GroupInterface> $routeGroups
     */
    public function __construct(
        string $method,
        string $path,
        RequestHandlerInterface|callable|string|array $requestHandler,
        ResponseFactoryInterface $responseFactory,
        array $routeGroups = [],
        ?ContainerInterface $container = null,
        int $identifier = 0
    ) {
        $this->method = $method;
        $this->path = $path;
        $this->requestHandler = $requestHandler;
        $this->groups = $routeGroups;

        $this->container = $container;

        $this->identifier = 'route' . $identifier;

        $this->middlewareDispatcher = new MiddlewareDispatcher($this, $this->container);

        $this->responseFactory = $responseFactory;
    }

    /**
     * @param RequestHandlerInterface|callable|string|array<string, string> $requestHandler
     * @param array<GroupInterface> $routeGroups
     */
    public static function create(
        string $method,
        string $path,
        RequestHandlerInterface|callable|string|array $requestHandler,
        ResponseFactoryInterface $responseFactory,
        array $routeGroups = [],
        ?ContainerInterface $container = null,
        int $identifier = 0
    ): self {
        return new self($method, $path, $requestHandler, $responseFactory, $routeGroups, $container, $identifier);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function add($middleware): RouteInterface
    {
        if (is_array($middleware)) {
            $middlewareArray = $middleware;
            foreach ($middlewareArray as $middleware) {
                $this->middlewareDispatcher->add($middleware);
            }
        } else {
            $this->middlewareDispatcher->add($middleware);
        }

        return $this;
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->groupMiddlewareAppended) {
            $this->appendGroupMiddlewareToRoute();
        }

        return $this->middlewareDispatcher->handle($request);
    }

    /**
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $requestHandler = $this->getRequestHandler();

        if (is_string($requestHandler) && $this->container) {
            $requestHandler = $this->container->get($requestHandler);
        }
        if (
            is_array($requestHandler) &&
            count($requestHandler) === 2 &&
            is_string($requestHandler[0]) && $this->container
        ) {
            $requestHandler[0] = $this->container->get($requestHandler[0]);
        }

        if ($requestHandler instanceof RequestHandlerInterface) {
            return $requestHandler->handle($request);
        }

        if (is_callable($requestHandler)) {
            if ($this->container && method_exists($this->container, 'call')) {
                return $this->container->call(
                    $requestHandler,
                    [
                        'request' => $request,
                        'response' => $this->responseFactory->createResponse(),
                        'args' => $this->getAttributes(),
                    ]
                );
            } else {
                return call_user_func_array(
                    $requestHandler,
                    (new InDepthArgumentsResolver($requestHandler))->resolve(
                        [
                            'request' => $request,
                            'response' => $this->responseFactory->createResponse(),
                            'args' => $this->getAttributes(),
                        ]
                    )
                );
            }
        }

        throw new RuntimeException("Can't run route");
    }

    protected function appendGroupMiddlewareToRoute(): void
    {
        $inner = $this->middlewareDispatcher;

        $this->middlewareDispatcher = new MiddlewareDispatcher($inner, $this->container);

        foreach ($this->groups as $group) {
            $group->appendMiddlewareToDispatcher($this->middlewareDispatcher);
        }

        $this->groupMiddlewareAppended = true;
    }

    public function withAttributes(array $attributes): RouteInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function name(string $name): RouteInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getRequestHandler(): RequestHandlerInterface|callable|string|array
    {
        return $this->requestHandler;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
