<?php

declare(strict_types=1);

namespace Framework\Tests\Router;

use Framework\Router\DispatchMiddleware;
use Framework\Router\RouteInterface;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DispatchMiddlewareTest extends TestCase
{
    use ProphecyTrait;

    public function testProcessWithoutRouterShouldHandleHandler(): void
    {
        $serverRequestProphecy = $this->prophesize(ServerRequestInterface::class);
        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $requestHandlerProphecy = $this->prophesize(RequestHandlerInterface::class);

        $requestHandlerProphecy
            ->handle($serverRequestProphecy->reveal())
            ->willReturn($responseProphecy->reveal());

        $dispatchMiddleware = new DispatchMiddleware();

        $this->assertSame(
            $requestHandlerProphecy->reveal()->handle($serverRequestProphecy->reveal()),
            $dispatchMiddleware->process($serverRequestProphecy->reveal(), $requestHandlerProphecy->reveal())
        );
    }

    public function testProcessWithRouterShouldRunRoute(): void
    {
        $serverRequestProphecy = $this->prophesize(ServerRequestInterface::class);
        $requestHandlerProphecy = $this->prophesize(RequestHandlerInterface::class);
        $routeProphecy = $this->prophesize(RouteInterface::class);

        $responseProphecy = $this->prophesize(ResponseInterface::class);
        $responseProphecy->getBody()->willReturn('Hello World');

        $routeProphecy->run($serverRequestProphecy->reveal())->willReturn($responseProphecy->reveal());

        $serverRequestProphecy->getAttribute(RouteInterface::class)->willReturn($routeProphecy->reveal());

        $dispatchMiddleware = new DispatchMiddleware();

        $response = $dispatchMiddleware->process($serverRequestProphecy->reveal(), $requestHandlerProphecy->reveal());

        $this->assertEquals('Hello World', (string) $response->getBody());
    }
}
