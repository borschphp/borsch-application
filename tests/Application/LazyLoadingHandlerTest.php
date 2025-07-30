<?php

namespace BorschTest\Application;

use Borsch\Application;
use Borsch\Container\Container;
use Borsch\RequestHandler\RequestHandler;
use Borsch\Router\{FastRouteRouter, RouterInterface};
use BorschTest\Middleware\{DispatchMiddleware, NotFoundHandlerMiddleware, RouteMiddleware};
use BorschTest\Mockup\{AMiddleware, CMiddleware, TestHandler};
use InvalidArgumentException;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use stdClass;

/**
 * @coversDefaultClass \Borsch\Application\Server\LazyLoadingHandler
 * @covers \Borsch\Application\Server\LazyLoadingHandler::__construct
 * @uses Application
 * @uses \Borsch\Application\Server\PipeMiddleware
 */
class LazyLoadingHandlerTest extends TestCase
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

        $this->application = new Application(new RequestHandler(), $container->get(RouterInterface::class), $container);
    }

    /**
     * @covers ::handle
     */
    public function testHandleNormally()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/to/get'
        );

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->get('/to/get', TestHandler::class);

        $response = $this->application->respond($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::handle
     */
    public function testHandleWithoutHandlerThrowsRuntimeException()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/to/get'
        );

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->get('/to/get', [
            AMiddleware::class,
            CMiddleware::class
        ]);

        $this->expectException(RuntimeException::class);
        $this->application->respond($server_request);
    }

    /**
     * @covers ::handle
     */
    public function testHandleWithInvalidObjectThrowsInvalidArgumentException()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/to/get'
        );

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->get('/to/get', [
            AMiddleware::class,
            CMiddleware::class,
            stdClass::class
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->application->respond($server_request);
    }
}
