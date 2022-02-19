<?php

namespace BorschTest\Application;

use Borsch\Application\App;
use Borsch\Application\PipeMiddleware;
use Borsch\Container\Container;
use Borsch\RequestHandler\RequestHandler;
use Borsch\Router\FastRouteRouter;
use Borsch\Router\RouterInterface;
use BorschTest\Middleware\DispatchMiddleware;
use BorschTest\Middleware\NotFoundHandlerMiddleware;
use BorschTest\Middleware\RouteMiddleware;
use BorschTest\Mockup\AMiddleware;
use BorschTest\Mockup\BMiddleware;
use BorschTest\Mockup\CMiddleware;
use BorschTest\Mockup\TestHandler;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Borsch\Application\PipeMiddleware
 * @covers \Borsch\Application\PipeMiddleware::__construct
 * @uses \Borsch\Application\App
 */
class PipeMiddlewareTest extends TestCase
{

    /** @var App */
    protected $app;

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

        $this->app = new App(
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
            $container
        );

        $request = (new ServerRequestFactory())->createServerRequest('GET', 'https://tests.com/to/test');

        $response = $pipe_middleware->process($request, new RequestHandler());

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(BMiddleware::class.'::process', (string)$response->getBody());
    }
}
