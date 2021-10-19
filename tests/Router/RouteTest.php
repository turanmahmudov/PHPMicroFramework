<?php

declare(strict_types=1);

namespace Framework\Tests\Router;

use Framework\Router\Route;
use Framework\Router\RouteInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteTest extends TestCase
{
    use ProphecyTrait;

    public function testShouldConstruct(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $requestHandlerProphecy = $this->prophesize(RequestHandlerInterface::class);

        $route = new Route(
            'POST',
            '/',
            $requestHandlerProphecy->reveal(),
            $responseFactoryProphecy->reveal()
        );

        $this->assertEquals('POST', $route->getMethod());
        $this->assertEquals('/', $route->getPath());
        $this->assertEquals($requestHandlerProphecy->reveal(), $route->getRequestHandler());
    }

    public function testCreate(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $requestHandlerProphecy = $this->prophesize(RequestHandlerInterface::class);

        $route = Route::create(
            'PUT',
            '/path',
            $requestHandlerProphecy->reveal(),
            $responseFactoryProphecy->reveal()
        );

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertEquals('PUT', $route->getMethod());
        $this->assertEquals('/path', $route->getPath());
        $this->assertEquals($requestHandlerProphecy->reveal(), $route->getRequestHandler());
    }

    public function testName(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $requestHandlerProphecy = $this->prophesize(RequestHandlerInterface::class);

        $route = Route::create(
            'PUT',
            '/path',
            $requestHandlerProphecy->reveal(),
            $responseFactoryProphecy->reveal()
        );

        $this->assertEquals('', $route->getName());

        $route->name('route-name');
        $this->assertEquals('route-name', $route->getName());
    }

    public function testNameShouldReturnRoute(): void
    {
        $responseFactoryProphecy = $this->prophesize(ResponseFactoryInterface::class);
        $requestHandlerProphecy = $this->prophesize(RequestHandlerInterface::class);

        $route = Route::create(
            'PUT',
            '/path',
            $requestHandlerProphecy->reveal(),
            $responseFactoryProphecy->reveal()
        );

        $route2 = $route->name('route-name');
        $this->assertInstanceOf(RouteInterface::class, $route2);
    }
}
