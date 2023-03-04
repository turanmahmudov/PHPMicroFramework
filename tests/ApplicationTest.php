<?php

declare(strict_types=1);

namespace Framework\Tests;

use Framework\Application;
use Framework\Http\Exception\MethodNotAllowedException;
use Framework\Http\Exception\NotFoundException;
use Framework\Middleware\EmptyPipelineHandler;
use Framework\Middleware\MiddlewareDispatcher;
use Framework\Middleware\MiddlewareDispatcherInterface;
use Framework\Router\DispatchMiddleware;
use Framework\Router\FastRoute\RouterFactory;
use Framework\Router\RouteCollector;
use Framework\Router\RouteCollectorInterface;
use Framework\Router\RouterInterface;
use Framework\Router\RouterMiddleware;
use Http\Discovery\Psr17Factory;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class ApplicationTest extends TestCase
{
    use ProphecyTrait;

    public function testGetContainer(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $app = new Application($responseFactoryProphecy->reveal(), $containerProphecy->reveal());

        $this->assertSame($containerProphecy->reveal(), $app->getContainer());
    }

    public function testGetResponseFactoryReturnsInjectedInstance(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $app = new Application($responseFactoryProphecy->reveal());

        $this->assertSame($responseFactoryProphecy->reveal(), $app->getResponseFactory());
    }

    public function testGetRouteCollectorReturnsInjectedInstance(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $routeCollectorProphecy = $this->prophesize(RouteCollectorInterface::class);
        $app = new Application(
            $responseFactoryProphecy->reveal(),
            null,
            null,
            $routeCollectorProphecy->reveal()
        );

        $this->assertSame($routeCollectorProphecy->reveal(), $app->getRouteCollector());
    }

    public function testSetRouteCollector(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $routeCollectorProphecy = $this->prophesize(RouteCollectorInterface::class);
        $app = new Application(
            $responseFactoryProphecy->reveal()
        );
        $app->setRouteCollector($routeCollectorProphecy->reveal());

        $this->assertSame($routeCollectorProphecy->reveal(), $app->getRouteCollector());
    }

    public function testCreatesRouteCollectorWhenNullWithInjectedContainer(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $router = (new RouterFactory())($containerProphecy->reveal());

        $routeCollector = new RouteCollector(
            $containerProphecy->reveal(),
            $responseFactoryProphecy->reveal(),
            $router
        );
        $app = new Application(
            $responseFactoryProphecy->reveal(),
            $containerProphecy->reveal(),
            null
        );

        $this->assertEquals($routeCollector, $app->getRouteCollector());
    }

    public function testGetDispatcherReturnsInjectedInstance(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $middlewareDispatcherProphecy = $this->prophesize(MiddlewareDispatcherInterface::class);

        $app = new Application(
            $responseFactoryProphecy->reveal(),
            null,
            $middlewareDispatcherProphecy->reveal()
        );

        $this->assertSame($middlewareDispatcherProphecy->reveal(), $app->getMiddlewareDispatcher());
    }

    public function testSetDispatcher(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $middlewareDispatcherProphecy = $this->prophesize(MiddlewareDispatcherInterface::class);

        $app = new Application(
            $responseFactoryProphecy->reveal()
        );
        $app->setMiddlewareDispatcher($middlewareDispatcherProphecy->reveal());

        $this->assertSame($middlewareDispatcherProphecy->reveal(), $app->getMiddlewareDispatcher());
    }

    public function testHandleReturnsErrorWhenNoDispatchMiddleware(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $requestProphecy = $this->prophesize(ServerRequestInterface::class);

        $app = new Application(
            $responseFactoryProphecy->reveal(),
        );
        $app->handle($requestProphecy->reveal());
    }

    public function testProcessReturnsErrorWhenNoDispatchMiddleware(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $requestHandlerProphecy = $this->prophesize(RequestHandlerInterface::class);
        $requestProphecy = $this->prophesize(ServerRequestInterface::class);

        $app = new Application(
            $responseFactoryProphecy->reveal(),
        );
        $app->process($requestProphecy->reveal(), $requestHandlerProphecy->reveal());
    }

    public function testHandleProxiesToDispatcherToHandle(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $requestProphecy = $this->prophesize(ServerRequestInterface::class);
        $responseProphecy = $this->prophesize(ResponseInterface::class);

        $middlewareDispatcherProphecy = $this->prophesize(MiddlewareDispatcherInterface::class);
        $middlewareDispatcherProphecy->handle($requestProphecy->reveal())->willReturn($responseProphecy->reveal());

        $app = new Application(
            $responseFactoryProphecy->reveal(),
            null,
            $middlewareDispatcherProphecy->reveal()
        );

        $this->assertSame($responseProphecy->reveal(), $app->handle($requestProphecy->reveal()));
    }

    public function testProcessProxiesToDispatcherToHandle(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $requestProphecy = $this->prophesize(ServerRequestInterface::class);
        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $requestHandlerProphecy = $this->prophesize(RequestHandlerInterface::class);

        $middlewareDispatcherProphecy = $this->prophesize(MiddlewareDispatcherInterface::class);
        $middlewareDispatcherProphecy->process(
            $requestProphecy->reveal(),
            $requestHandlerProphecy->reveal()
        )->willReturn($responseProphecy->reveal());

        $app = new Application(
            $responseFactoryProphecy->reveal(),
            null,
            $middlewareDispatcherProphecy->reveal()
        );

        $this->assertSame($responseProphecy->reveal(), $app->process(
            $requestProphecy->reveal(),
            $requestHandlerProphecy->reveal()
        ));
    }

    public function testAddProxiesToDispatcherToAdd(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $requestProphecy = $this->prophesize(ServerRequestInterface::class);
        $responseProphecy = $this->prophesize(ResponseInterface::class);

        $responseProphecy->getBody()->willReturn('Hello World');

        $middlewareProphecy = $this->prophesize(MiddlewareInterface::class);
        $middlewareProphecy->process(Argument::any(), Argument::any())->willReturn($responseProphecy->reveal());

        $middlewareDispatcher = new MiddlewareDispatcher(new EmptyPipelineHandler(__CLASS__));
        $middlewareDispatcher->add($middlewareProphecy->reveal());

        $app = new Application(
            $responseFactoryProphecy->reveal()
        );

        $this->assertSame(
            $middlewareDispatcher->handle($requestProphecy->reveal()),
            $app->add($middlewareProphecy->reveal())->handle($requestProphecy->reveal())
        );
    }

    /**
     * @dataProvider upperCaseRequestMethodsProvider
     */
    public function testGetPostPutPatchDeleteOptionsMethods(string $method): void
    {
        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $responseProphecy->getBody()->willReturn('Hello World');

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $responseFactoryProphecy->createResponse()->willReturn($responseProphecy->reveal());

        $request = $this->generateServerRequest($method);

        $methodName = strtolower($method);
        $app = new Application(
            $responseFactoryProphecy->reveal()
        );
        $app->{$methodName}('/', function (ServerRequestInterface $request) use ($responseFactoryProphecy) {
            return $responseFactoryProphecy->reveal()->createResponse();
        });

        $app->add(new RouterMiddleware($app->getRouteCollector()));
        $app->add(DispatchMiddleware::class);
        $response = $app->handle($request);

        $this->assertEquals('Hello World', (string) $response->getBody());
    }

    public function testRouteMethodNotAllowed(): void
    {
        $this->expectException(MethodNotAllowedException::class);

        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $responseProphecy->getBody()->willReturn('Hello World');

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $responseFactoryProphecy->createResponse()->willReturn($responseProphecy->reveal());

        $request = $this->generateServerRequest('POST');

        $app = new Application(
            $responseFactoryProphecy->reveal()
        );
        $app->get('/', function (ServerRequestInterface $request) use ($responseFactoryProphecy) {
            return $responseFactoryProphecy->reveal()->createResponse();
        });

        $app->add(new RouterMiddleware($app->getRouteCollector()));
        $app->add(DispatchMiddleware::class);
        $app->handle($request);
    }

    public function testRouteMatching(): void
    {
        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $responseProphecy->getBody()->willReturn('Hello World');

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $responseFactoryProphecy->createResponse()->willReturn($responseProphecy->reveal());

        $request = $this->generateServerRequest();

        $app = new Application(
            $responseFactoryProphecy->reveal()
        );
        $app->get('/', function (ServerRequestInterface $request) use ($responseFactoryProphecy) {
            return $responseFactoryProphecy->reveal()->createResponse();
        });

        $app->add(new RouterMiddleware($app->getRouteCollector()));
        $app->add(DispatchMiddleware::class);
        $response = $app->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('Hello World', (string) $response->getBody());
    }

    public function testRouteMatchingWithNamedParam(): void
    {
        $streamProphecy = $this->prophesize(StreamInterface::class);
        $streamProphecy->__toString()->willReturn('');
        $streamProphecy->write(Argument::type('string'))->will(function ($args): void {
            $body = $this->reveal()->__toString();
            $body .= $args[0];
            $this->__toString()->willReturn($body);
        });

        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $responseProphecy->getBody()->willReturn($streamProphecy->reveal());

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $responseFactoryProphecy->createResponse()->willReturn($responseProphecy->reveal());

        $request = $this->generateServerRequest('GET', '/hello/World');

        $app = new Application(
            $responseFactoryProphecy->reveal()
        );
        $app->get('/hello/{name}', function (
            ServerRequestInterface $request,
            array $args = []
        ) use ($responseFactoryProphecy) {
            $response = $responseFactoryProphecy->reveal()->createResponse();
            $response->getBody()->write("Hello {$args['name']}");

            return $response;
        });

        $app->add(new RouterMiddleware($app->getRouteCollector()));
        $app->add(DispatchMiddleware::class);
        $response = $app->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('Hello World', (string) $response->getBody());
    }

    public function testRouteNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);

        $request = $this->generateServerRequest();

        $app = new Application(
            $responseFactoryProphecy->reveal()
        );
        $app->add(new RouterMiddleware($app->getRouteCollector()));
        $app->add(DispatchMiddleware::class);
        $app->handle($request);
    }

    public function testRouteMatchingWithCallableRegisteredInContainer(): void
    {
        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $responseProphecy->getBody()->willReturn('Hello World');

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $responseFactoryProphecy->createResponse()->willReturn($responseProphecy->reveal());

        $handler = new class ($responseFactoryProphecy) {
            private $responseFactoryProphecy;

            public function __construct($responseFactoryProphecy)
            {
                $this->responseFactoryProphecy = $responseFactoryProphecy;
            }

            public function __invoke(ServerRequestInterface $request)
            {
                return $this->responseFactoryProphecy->reveal()->createResponse();
            }
        };

        $request = $this->generateServerRequest();

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->has('handler')->willReturn(true);
        $containerProphecy->get('handler')->willReturn($handler);
        $containerProphecy->has('config')->willReturn(false);
        $containerProphecy->has(RouterInterface::class)->willReturn(false);

        $app = new Application(
            $responseFactoryProphecy->reveal(),
            $containerProphecy->reveal()
        );
        $app->get('/', 'handler');

        $app->add(new RouterMiddleware($app->getRouteCollector()));
        $app->add(new DispatchMiddleware());
        $response = $app->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('Hello World', (string) $response->getBody());
    }

    public function testRouteMatchingWithArrayHandlerRegisteredInContainer(): void
    {
        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $responseProphecy->getBody()->willReturn('Hello World');

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $responseFactoryProphecy->createResponse()->willReturn($responseProphecy->reveal());

        $handler = new class ($responseFactoryProphecy) {
            private $responseFactoryProphecy;

            public function __construct($responseFactoryProphecy)
            {
                $this->responseFactoryProphecy = $responseFactoryProphecy;
            }

            public function foo(ServerRequestInterface $request)
            {
                return $this->responseFactoryProphecy->reveal()->createResponse();
            }
        };

        $request = $this->generateServerRequest();

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->has('handler')->willReturn(true);
        $containerProphecy->get('handler')->willReturn($handler);
        $containerProphecy->has('config')->willReturn(false);
        $containerProphecy->has(RouterInterface::class)->willReturn(false);

        $app = new Application(
            $responseFactoryProphecy->reveal(),
            $containerProphecy->reveal()
        );
        $app->get('/', ['handler', 'foo']);

        $app->add(new RouterMiddleware($app->getRouteCollector()));
        $app->add(new DispatchMiddleware());
        $response = $app->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('Hello World', (string) $response->getBody());
    }

    public function testRouteMatchingWithNonExistedMethodInArrayHandlerRegisteredInContainer(): void
    {
        $this->expectException(RuntimeException::class);

        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $responseProphecy->getBody()->willReturn('Hello World');

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $responseFactoryProphecy->createResponse()->willReturn($responseProphecy->reveal());

        $handler = new class () {
        };

        $request = $this->generateServerRequest();

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->has('handler')->willReturn(true);
        $containerProphecy->get('handler')->willReturn($handler);
        $containerProphecy->has('config')->willReturn(false);
        $containerProphecy->has(RouterInterface::class)->willReturn(false);

        $app = new Application(
            $responseFactoryProphecy->reveal(),
            $containerProphecy->reveal()
        );
        $app->get('/', ['handler', 'foo']);

        $app->add(new RouterMiddleware($app->getRouteCollector()));
        $app->add(new DispatchMiddleware());
        $app->handle($request);
    }

    public function testRouteMatchingWithInvokeFunctionNameNoContainer()
    {
        $streamProphecy = $this->prophesize(StreamInterface::class);
        $streamProphecy->__toString()->willReturn('');
        $streamProphecy->write(Argument::type('string'))->will(function ($args): void {
            $body = $this->reveal()->__toString();
            $body .= $args[0];
            $this->__toString()->willReturn($body);
        });

        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $responseProphecy->getBody()->willReturn($streamProphecy->reveal());

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $responseFactoryProphecy->createResponse()->willReturn($responseProphecy->reveal());

        // @codingStandardsIgnoreStart
        function handle($request, ResponseInterface $response)
        {
            $response->getBody()->write('Hello World');

            return $response;
        }

        $request = $this->generateServerRequest();

        $app = new Application(
            $responseFactoryProphecy->reveal()
        );
        $app->get('/', __NAMESPACE__.'\handle');

        $app->add(new RouterMiddleware($app->getRouteCollector()));
        $app->add(DispatchMiddleware::class);
        $response = $app->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals('Hello World', (string) $response->getBody());
    }

    /**
     * @dataProvider routeGroupsDataProvider
     */
    public function testRouteGroupCombinations(array $sequence, string $expectedPath): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $app = new Application($responseFactoryProphecy->reveal());

        $processSequence = function (Application $app, array $sequence, $processSequence): void {
            $path = array_shift($sequence);

            if (count($sequence)) {
                $app->group($path, function () use ($app, &$sequence, $processSequence): void {
                    $processSequence($app, $sequence, $processSequence);
                });
            } else {
                $app->get($path, function (): void {
                });
            }
        };

        $processSequence($app, $sequence, $processSequence);

        $routeCollector = $app->getRouteCollector();
        $route = $routeCollector->lookupRoute('route0');

        $this->assertEquals($expectedPath, $route->getPath());
    }

    public function testRouteGroupPattern(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);

        /** @var ResponseFactoryInterface $responseFactoryInterface */
        $responseFactoryInterface = $responseFactoryProphecy->reveal();
        $app = new Application($responseFactoryInterface);
        $group = $app->group('/foo', function (): void {
        });

        $this->assertEquals('/foo', $group->getPath());
    }

    /**
     * @runInSeparateProcess
     */
    public function testRun(): void
    {
        $streamProphecy = $this->prophesize(StreamInterface::class);
        $streamProphecy->__toString()->willReturn('');
        $streamProphecy->write(Argument::type('string'))->will(function ($args): void {
            $body = $this->reveal()->__toString();
            $body .= $args[0];
            $this->__toString()->willReturn($body);
        });
        $streamProphecy->read(1)->willReturn('_');
        $streamProphecy->read('11')->will(function () {
            $this->eof()->willReturn(true);

            return $this->reveal()->__toString();
        });
        $streamProphecy->eof()->willReturn(false);
        $streamProphecy->isSeekable()->willReturn(true);
        $streamProphecy->rewind()->willReturn(true);

        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $responseProphecy->getBody()->willReturn($streamProphecy->reveal());
        $responseProphecy->getStatusCode()->willReturn(200);
        $responseProphecy->getHeaders()->willReturn(['Content-Length' => ['11']]);
        $responseProphecy->getProtocolVersion()->willReturn('1.1');
        $responseProphecy->getReasonPhrase()->willReturn('');
        $responseProphecy->getHeaderLine('Content-Length')->willReturn('11');

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $responseFactoryProphecy->createResponse()->willReturn($responseProphecy->reveal());

        $request = $this->generateServerRequest();

        $app = new Application(
            $responseFactoryProphecy->reveal()
        );
        $app->get('/', function (ServerRequestInterface $request) use ($responseFactoryProphecy) {
            $response = $responseFactoryProphecy->reveal()->createResponse();
            $response->getBody()->write('Hello World');

            return $response;
        });

        $app->add(new RouterMiddleware($app->getRouteCollector()));
        $app->add(DispatchMiddleware::class);

        $app->run($request);

        $this->expectOutputString('Hello World');
    }

    /**
     * @runInSeparateProcess
     */
    public function testRunWithoutPassingServerRequest(): void
    {
        $streamProphecy = $this->prophesize(StreamInterface::class);
        $streamProphecy->__toString()->willReturn('');
        $streamProphecy->write(Argument::type('string'))->will(function ($args): void {
            $body = $this->reveal()->__toString();
            $body .= $args[0];
            $this->__toString()->willReturn($body);
        });
        $streamProphecy->read(1)->willReturn('_');
        $streamProphecy->read('11')->will(function () {
            $this->eof()->willReturn(true);

            return $this->reveal()->__toString();
        });
        $streamProphecy->eof()->willReturn(false);
        $streamProphecy->isSeekable()->willReturn(true);
        $streamProphecy->rewind()->willReturn(true);

        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $responseProphecy->getBody()->willReturn($streamProphecy->reveal());
        $responseProphecy->getStatusCode()->willReturn(200);
        $responseProphecy->getHeaders()->willReturn(['Content-Length' => ['11']]);
        $responseProphecy->getProtocolVersion()->willReturn('1.1');
        $responseProphecy->getReasonPhrase()->willReturn('');
        $responseProphecy->getHeaderLine('Content-Length')->willReturn('11');

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $responseFactoryProphecy->createResponse()->willReturn($responseProphecy->reveal());

        $request = $this->generateServerRequest();

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(ServerRequestInterface::class)->willReturn($request);
        $containerProphecy->has('config')->willReturn(false);
        $containerProphecy->has(RouterInterface::class)->willReturn(false);

        $app = new Application(
            $responseFactoryProphecy->reveal(),
            $containerProphecy->reveal()
        );
        $app->get('/', function (ServerRequestInterface $request) use ($responseFactoryProphecy) {
            $response = $responseFactoryProphecy->reveal()->createResponse();
            $response->getBody()->write('Hello World');

            return $response;
        });

        $app->add(new RouterMiddleware($app->getRouteCollector()));
        $app->add(new DispatchMiddleware());

        $app->run();

        $this->expectOutputString('Hello World');
    }

    /**
     * @runInSeparateProcess
     */
    public function testRunWithoutPassingServerRequestAndWithoutContainer(): void
    {
        $streamProphecy = $this->prophesize(StreamInterface::class);
        $streamProphecy->__toString()->willReturn('');
        $streamProphecy->write(Argument::type('string'))->will(function ($args): void {
            $body = $this->reveal()->__toString();
            $body .= $args[0];
            $this->__toString()->willReturn($body);
        });
        $streamProphecy->read(1)->willReturn('_');
        $streamProphecy->read('11')->will(function () {
            $this->eof()->willReturn(true);

            return $this->reveal()->__toString();
        });
        $streamProphecy->eof()->willReturn(false);
        $streamProphecy->isSeekable()->willReturn(true);
        $streamProphecy->rewind()->willReturn(true);

        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $responseProphecy->getBody()->willReturn($streamProphecy->reveal());
        $responseProphecy->getStatusCode()->willReturn(200);
        $responseProphecy->getHeaders()->willReturn(['Content-Length' => ['11']]);
        $responseProphecy->getProtocolVersion()->willReturn('1.1');
        $responseProphecy->getReasonPhrase()->willReturn('');
        $responseProphecy->getHeaderLine('Content-Length')->willReturn('11');

        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $responseFactoryProphecy->createResponse()->willReturn($responseProphecy->reveal());

        $app = new Application(
            $responseFactoryProphecy->reveal()
        );
        $app->get('[/]', function (ServerRequestInterface $request) use ($responseFactoryProphecy) {
            $response = $responseFactoryProphecy->reveal()->createResponse();
            $response->getBody()->write('Hello World');

            return $response;
        });

        $app->add(new RouterMiddleware($app->getRouteCollector()));
        $app->add(new DispatchMiddleware());

        $app->run();

        $this->expectOutputString('Hello World');
    }

    public function upperCaseRequestMethodsProvider()
    {
        return [
            ['GET'],
            ['POST'],
            ['PUT'],
            ['PATCH'],
            ['DELETE'],
            ['OPTIONS'],
            ['HEAD'],
        ];
    }

    public function routeGroupsDataProvider()
    {
        return [
            'empty group with empty route' => [
                ['', ''], '',
            ],
            'empty group with single slash route' => [
                ['', '/'], '/',
            ],
            'empty group with segment route that does not end in aSlash' => [
                ['', '/foo'], '/foo',
            ],
            'empty group with segment route that ends in aSlash' => [
                ['', '/foo/'], '/foo/',
            ],
            'group single slash with empty route' => [
                ['/', ''], '/',
            ],
            'group single slash with single slash route' => [
                ['/', '/'], '//',
            ],
            'group single slash with segment route that does not end in aSlash' => [
                ['/', '/foo'], '//foo',
            ],
            'group single slash with segment route that ends in aSlash' => [
                ['/', '/foo/'], '//foo/',
            ],
            'group segment with empty route' => [
                ['/foo', ''], '/foo',
            ],
            'group segment with single slash route' => [
                ['/foo', '/'], '/foo/',
            ],
            'group segment with segment route that does not end in aSlash' => [
                ['/foo', '/bar'], '/foo/bar',
            ],
            'group segment with segment route that ends in aSlash' => [
                ['/foo', '/bar/'], '/foo/bar/',
            ],
            'empty group with nested group segment with an empty route' => [
                ['', '/foo', ''], '/foo',
            ],
            'empty group with nested group segment with single slash route' => [
                ['', '/foo', '/'], '/foo/',
            ],
            'group single slash with empty nested group and segment route without leading slash' => [
                ['/', '', 'foo'], '/foo',
            ],
            'group single slash with empty nested group and segment route' => [
                ['/', '', '/foo'], '//foo',
            ],
            'group single slash with single slash group and segment route without leading slash' => [
                ['/', '/', 'foo'], '//foo',
            ],
            'group single slash with single slash nested group and segment route' => [
                ['/', '/', '/foo'], '///foo',
            ],
            'group single slash with nested group segment with an empty route' => [
                ['/', '/foo', ''], '//foo',
            ],
            'group single slash with nested group segment with single slash route' => [
                ['/', '/foo', '/'], '//foo/',
            ],
            'group single slash with nested group segment with segment route' => [
                ['/', '/foo', '/bar'], '//foo/bar',
            ],
            'group single slash with nested group segment with segment route that has aTrailing slash' => [
                ['/', '/foo', '/bar/'], '//foo/bar/',
            ],
            'empty group with empty nested group and segment route without leading slash' => [
                ['', '', 'foo'], 'foo',
            ],
            'empty group with empty nested group and segment route' => [
                ['', '', '/foo'], '/foo',
            ],
            'empty group with single slash group and segment route without leading slash' => [
                ['', '/', 'foo'], '/foo',
            ],
            'empty group with single slash nested group and segment route' => [
                ['', '/', '/foo'], '//foo',
            ],
            'empty group with nested group segment with segment route' => [
                ['', '/foo', '/bar'], '/foo/bar',
            ],
            'empty group with nested group segment with segment route that has aTrailing slash' => [
                ['', '/foo', '/bar/'], '/foo/bar/',
            ],
            'group segment with empty nested group and segment route without leading slash' => [
                ['/foo', '', 'bar'], '/foobar',
            ],
            'group segment with empty nested group and segment route' => [
                ['/foo', '', '/bar'], '/foo/bar',
            ],
            'group segment with single slash nested group and segment route' => [
                ['/foo', '/', 'bar'], '/foo/bar',
            ],
            'group segment with single slash nested group and slash segment route' => [
                ['/foo', '/', '/bar'], '/foo//bar',
            ],
            'two group segments with empty route' => [
                ['/foo', '/bar', ''], '/foo/bar',
            ],
            'two group segments with single slash route' => [
                ['/foo', '/bar', '/'], '/foo/bar/',
            ],
            'two group segments with segment route' => [
                ['/foo', '/bar', '/baz'], '/foo/bar/baz',
            ],
            'two group segments with segment route that has aTrailing slash' => [
                ['/foo', '/bar', '/baz/'], '/foo/bar/baz/',
            ],
        ];
    }

    private function generateServerRequest(string $method = 'GET', string $uri = '/'): ServerRequestInterface
    {
        $factory = new Psr17Factory();
        return $factory->createServerRequest(
            $method,
            $uri,
            ['REQUEST_METHOD' => $method]
        );
    }
}
