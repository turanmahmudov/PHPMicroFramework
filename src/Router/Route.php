<?php

declare(strict_types=1);

namespace Framework\Router;

use ArgumentsResolver\InDepthArgumentsResolver;
use Framework\Middleware\MiddlewareDispatcher;
use Framework\Middleware\MiddlewareDispatcherInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionException;
use RuntimeException;

final class Route implements RouteInterface
{
    /**
     * @var string
     */
    protected string $identifier;

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var string
     */
    protected string $method;

    /**
     * @var string
     */
    protected string $path;

    /** @var string|callable|array|RequestHandlerInterface|MiddlewareInterface */
    protected $requestHandler;

    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * @var array<GroupInterface>
     */
    protected array $groups;

    /**
     * @var MiddlewareDispatcherInterface
     */
    protected MiddlewareDispatcherInterface $middlewareDispatcher;

    /**
     * @var ContainerInterface|null
     */
    protected ?ContainerInterface $container;

    /**
     * @var ResponseFactoryInterface
     */
    protected ResponseFactoryInterface $responseFactory;

    /**
     * @var bool
     */
    protected bool $groupMiddlewareAppended = false;

    /**
     * @param string $method
     * @param string $path
     * @param string|callable|array|RequestHandlerInterface|MiddlewareInterface $requestHandler
     * @param ResponseFactoryInterface $responseFactory
     * @param array<GroupInterface> $routeGroups
     * @param ContainerInterface|null $container
     * @param int $identifier
     */
    public function __construct(
        string $method,
        string $path,
        $requestHandler,
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
     * @param string $method
     * @param string $path
     * @param string|callable|array|RequestHandlerInterface $requestHandler
     * @param ResponseFactoryInterface $responseFactory
     * @param array<GroupInterface> $routeGroups
     * @param ContainerInterface|null $container
     * @param int $identifier
     * @return Route
     */
    public static function create(
        string $method,
        string $path,
        $requestHandler,
        ResponseFactoryInterface $responseFactory,
        array $routeGroups = [],
        ?ContainerInterface $container = null,
        int $identifier = 0
    ): self {
        return new self($method, $path, $requestHandler, $responseFactory, $routeGroups, $container, $identifier);
    }

    /**
     * {@inheritDoc}
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

    /**
     * {@inheritDoc}
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->groupMiddlewareAppended) {
            $this->appendGroupMiddlewareToRoute();
        }

        return $this->middlewareDispatcher->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ReflectionException
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
                return call_user_func_array($requestHandler, (new InDepthArgumentsResolver($requestHandler))->resolve([
                    'request' => $request,
                    'response' => $this->responseFactory->createResponse(),
                    'args' => $this->getAttributes()
                ]));
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

    public function getRequestHandler()
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
