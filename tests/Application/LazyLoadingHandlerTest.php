<?php

namespace BorschTest\Application;

use Borsch\Application\App;
use Borsch\Container\Container;
use Borsch\RequestHandler\RequestHandler;
use Borsch\Router\FastRouteRouter;
use Borsch\Router\RouterInterface;
use BorschTest\Middleware\DispatchMiddleware;
use BorschTest\Middleware\NotFoundHandlerMiddleware;
use BorschTest\Middleware\RouteMiddleware;
use BorschTest\Mockup\AMiddleware;
use BorschTest\Mockup\CMiddleware;
use BorschTest\Mockup\TestHandler;
use InvalidArgumentException;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use stdClass;

/**
 * @coversDefaultClass \Borsch\Application\LazyLoadingHandler
 * @covers \Borsch\Application\LazyLoadingHandler::__construct
 * @uses \Borsch\Application\App
 * @uses \Borsch\Application\PipeMiddleware
 */
class LazyLoadingHandlerTest extends TestCase
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

        $this->app = new class(new RequestHandler(), $container->get(RouterInterface::class), $container) extends App {
            public function runAndGetResponse(ServerRequestInterface $server_request): ResponseInterface
            {
                return $this->request_handler->handle($server_request);
            }
        };
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

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->get('/to/get', TestHandler::class);

        $response = $this->app->runAndGetResponse($server_request);

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

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->get('/to/get', [
            AMiddleware::class,
            CMiddleware::class
        ]);

        $this->expectException(RuntimeException::class);
        $this->app->runAndGetResponse($server_request);
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

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->get('/to/get', [
            AMiddleware::class,
            CMiddleware::class,
            stdClass::class
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->app->runAndGetResponse($server_request);
    }
}
