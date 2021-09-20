<?php

declare(strict_types=1);

namespace Framework\Tests\Router;

use Framework\Middleware\MiddlewareDispatcher;
use Framework\Router\Group;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Http\Server\RequestHandlerInterface;

class GroupTest extends TestCase
{
    use ProphecyTrait;

    public function testCreateShouldConstruct(): void
    {
        $group = Group::create('/', function () {
        });

        $this->assertInstanceOf(Group::class, $group);
        $this->assertEquals('/', $group->getPath());
    }

    public function testAddMiddleware(): void
    {
        $group = Group::create('/', function () {
        });

        $this->assertCount(0, $group->getMiddlewares());

        $group->add('middleware');
        $this->assertCount(1, $group->getMiddlewares());

        $group->add(['middleware', 'middleware']);
        $this->assertCount(3, $group->getMiddlewares());
    }

    public function testGetRoutesShouldCallCallbackAndReturnGroup(): void
    {
        $output = '';
        $group = Group::create('/', function () use (&$output) {
            $output = 'Hello World';
        });

        $this->assertEquals($group, $group->getRoutes());
        $this->assertEquals('Hello World', $output);
    }

    public function testAppendMiddlewareToDispatcher(): void
    {
        $requestHandlerProphecy = $this->prophesize(RequestHandlerInterface::class);

        $middlewareDispatcher = new MiddlewareDispatcher($requestHandlerProphecy->reveal());
        $middlewareDispatcher->add(function () {
        });

        $this->assertEquals(1, $middlewareDispatcher->getMiddleware()->count());

        $group = Group::create('/', function () {
        });
        $group->add([
            function () {
            },
            function () {
            }
        ]);

        $group->appendMiddlewareToDispatcher($middlewareDispatcher);

        $this->assertEquals(3, $middlewareDispatcher->getMiddleware()->count());
    }
}
