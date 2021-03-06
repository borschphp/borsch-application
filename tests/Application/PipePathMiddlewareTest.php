<?php
/**
 * @author debuss-a
 */

namespace BorschTest\Application;

require_once __DIR__.'/../../vendor/autoload.php';

use Borsch\Application\App;
use Borsch\Application\PipePathMiddleware;
use Borsch\Container\Container;
use Borsch\RequestHandler\RequestHandler;
use Borsch\Router\FastRouteRouter;
use Borsch\Router\RouterInterface;
use BorschTest\Middleware\DispatchMiddleware;
use BorschTest\Middleware\NotFoundHandlerMiddleware;
use BorschTest\Middleware\PipedMiddleware;
use BorschTest\Middleware\RouteMiddleware;
use BorschTest\Mockup\AMiddleware;
use BorschTest\Mockup\CMiddleware;
use BorschTest\Mockup\TestHandler;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @coversDefaultClass \Borsch\Application\PipePathMiddleware
 * @covers \Borsch\Application\PipePathMiddleware::__construct
 * @uses \Borsch\Application\PipePathMiddleware
 * @uses \Borsch\Application\App
 * @uses \Borsch\Application\PipeMiddleware
 * @uses \Borsch\Application\LazyLoadingHandler
 */
class PipePathMiddlewareTest extends TestCase
{

    /** @var App */
    protected $app;

    public function setUp(): void
    {
        $container = new Container();
        $container->set(PipePathMiddleware::class);
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
    public function testProcessWithPath()
    {
        $request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/to/test'
        );

        $container = new Container();
        $handler = new RequestHandler();
        $middleware = new PipePathMiddleware('/to', PipedMiddleware::class, $container);

        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(PipedMiddleware::class.'::process', $response->getBody()->getContents());
    }

    /**
     * @covers ::process
     */
    public function testProcessWithWrongPath()
    {
        $request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/test/with/wrong/path'
        );

        $container = new Container();
        $handler = new RequestHandler();
        $handler->middleware(new NotFoundHandlerMiddleware());
        $middleware = new PipePathMiddleware('/to', PipedMiddleware::class, $container);

        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @covers ::process
     */
    public function testProcessWithPathNotPlacedAtTheBeginningOrUri()
    {
        $request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/test/with/to/path'
        );

        $container = new Container();
        $handler = new RequestHandler();
        $handler->middleware(new NotFoundHandlerMiddleware());
        $middleware = new PipePathMiddleware('/to', PipedMiddleware::class, $container);

        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * @covers ::process
     */
    public function testProcessWithPathAndArrayOfMiddleware()
    {
        $request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/to/test'
        );

        $this->app->pipe('/to', [
            AMiddleware::class,
            CMiddleware::class
        ]);

        $this->app->get('/to/test', TestHandler::class);

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $response = $this->app->runAndGetResponse($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }
}
