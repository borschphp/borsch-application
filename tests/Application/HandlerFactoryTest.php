<?php

namespace BorschTest\Application;

use Borsch\Application\Exception\ApplicationInvalidArgumentException;
use Borsch\Application\Factory\HandlerFactory;
use Borsch\Container\Container;
use Borsch\Router\FastRouteRouter;
use Borsch\Router\RouterInterface;
use BorschTest\Middleware\DispatchMiddleware;
use BorschTest\Middleware\NotFoundHandlerMiddleware;
use BorschTest\Middleware\RouteMiddleware;
use BorschTest\Mockup\AMiddleware;
use BorschTest\Mockup\CMiddleware;
use BorschTest\Mockup\TestHandler;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass HandlerFactory
 * @covers HandlerFactory::__construct
 * @uses HandlerFactory
 */
class HandlerFactoryTest extends TestCase
{

    protected HandlerFactory $handler_factory;

    public function setUp(): void
    {
        $container = new Container();
        $container->set(RouteMiddleware::class);
        $container->set(DispatchMiddleware::class);
        $container->set(NotFoundHandlerMiddleware::class);
        $container->set(TestHandler::class);
        $container->set(FastRouteRouter::class);
        $container->set(RouterInterface::class, FastRouteRouter::class)->cache(true);
        $container->set(AMiddleware::class);
        $container->set(CMiddleware::class);

        $this->handler_factory = new HandlerFactory($container);
    }

    public function testCreate()
    {
        $handler = $this->handler_factory->create(TestHandler::class);

        $this->assertInstanceOf(TestHandler::class, $handler);
    }

    public function testCreateThrowException()
    {
        $this->expectException(ApplicationInvalidArgumentException::class);

        $this->handler_factory->create(FastRouteRouter::class);
    }
}