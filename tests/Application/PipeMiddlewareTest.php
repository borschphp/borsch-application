<?php

namespace BorschTest\Application;

use Borsch\Application\Application;
use Borsch\Container\Container;
use Borsch\Application\Factory\HandlerFactory;
use Borsch\RequestHandler\RequestHandler;
use Borsch\Router\{FastRouteRouter, RouterInterface};
use Borsch\Application\Server\PipeMiddleware;
use BorschTest\Middleware\{DispatchMiddleware, NotFoundHandlerMiddleware, RouteMiddleware};
use BorschTest\Mockup\{AMiddleware, BMiddleware, CMiddleware, TestHandler};
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Borsch\Application\Server\PipeMiddleware
 * @covers \Borsch\Application\Server\PipeMiddleware::__construct
 * @uses Application
 */
class PipeMiddlewareTest extends TestCase
{

    protected Application $application;

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

        $this->application = new Application(
            new RequestHandler(),
            $container->get(RouterInterface::class),
            $container
        );
    }

    /**
     * @covers ::process
     */
    public function testProcess()
    {
        $container = new Container();
        $pipe_middleware = new PipeMiddleware(
            '/',
            BMiddleware::class,
            new HandlerFactory($container)
        );

        $request = (new ServerRequestFactory())->createServerRequest('GET', 'https://tests.com/to/test');

        $response = $pipe_middleware->process($request, new RequestHandler());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(BMiddleware::class.'::process', (string)$response->getBody());
    }
}
