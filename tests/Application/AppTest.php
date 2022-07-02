<?php
/**
 * @author debuss-a
 */

namespace BorschTest\Application;

require_once __DIR__.'/../../vendor/autoload.php';

use Borsch\Application\App;
use Borsch\Application\ApplicationInterface;
use Borsch\Container\Container;
use Borsch\RequestHandler\RequestHandler;
use Borsch\Router\FastRouteRouter;
use Borsch\Router\RouterInterface;
use BorschTest\Middleware\DispatchMiddleware;
use BorschTest\Middleware\NotFoundHandlerMiddleware;
use BorschTest\Middleware\PipedMiddleware;
use BorschTest\Middleware\RouteMiddleware;
use BorschTest\Mockup\AMiddleware;
use BorschTest\Mockup\BMiddleware;
use BorschTest\Mockup\CMiddleware;
use BorschTest\Mockup\TestHandler;
use InvalidArgumentException;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use stdClass;
use TypeError;

/**
 * @coversDefaultClass \Borsch\Application\App
 * @covers \Borsch\Application\App::__construct
 * @covers \Borsch\Application\App::runAndGetResponse
 * @covers \Borsch\Application\App::run
 * @uses \Borsch\Application\App
 * @uses \Borsch\Application\PipePathMiddleware
 * @uses \Borsch\Application\PipeMiddleware
 * @uses \Borsch\Application\LazyLoadingHandler
 * @uses \Borsch\RequestHandler\Emitter
 * @uses \Borsch\RequestHandler\RequestHandler
 */
class AppTest extends TestCase
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
        $container->set(stdClass::class, function () {
            return new stdClass();
        });

        $this->app = new class(new RequestHandler(), $container->get(RouterInterface::class), $container) extends App {
            public function runAndGetResponse(ServerRequestInterface $server_request): ResponseInterface
            {
                return $this->request_handler->handle($server_request);
            }
        };
    }

    /**
     * @covers ::__construct
     */
    public function test__construct()
    {
        $this->assertInstanceOf(ApplicationInterface::class, $this->app);
    }

    /**
     * @covers ::pipe
     */
    public function testPipeWithoutPath()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'DELETE',
            'https://tests.com/to/delete'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->delete('/to/delete', TestHandler::class);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::pipe
     */
    public function testPipeWithSegregatedPath()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'DELETE',
            'https://tests.com/to/delete'
        );

        $this->app->pipe('/to', PipedMiddleware::class);

        $this->app->delete('/to/delete', TestHandler::class);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(PipedMiddleware::class.'::process', $response->getBody()->getContents());
    }

    /**
     * @covers ::pipe
     */
    public function testPipeWithSegregatedPathAndArrayOfMiddlewares()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/to/test'
        );

        $this->app->pipe( '/to/', [
            AMiddleware::class,
            BMiddleware::class
        ]);

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(BMiddleware::class.'::process', $response->getBody()->getContents());
        $this->assertEquals('TEST', $response->getHeaderLine('X-Test'));
    }

    /**
     * @covers ::pipe
     */
    public function testNoSegregatedPathAndArrayOfMiddlewaresReturnsException()
    {
        $this->expectException(TypeError::class);

        $this->app->pipe([
            RouteMiddleware::class,
            DispatchMiddleware::class,
            NotFoundHandlerMiddleware::class
        ]);
    }

    /**
     * @covers ::delete
     */
    public function testDelete()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'DELETE',
            'https://tests.com/to/delete'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->delete('/to/delete', TestHandler::class);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::connect
     */
    public function testConnect()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'CONNECT',
            'https://tests.com/to/connect'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->connect('/to/connect', TestHandler::class);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::post
     */
    public function testPost()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'POST',
            'https://tests.com/to/post'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->post('/to/post', TestHandler::class);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::put
     */
    public function testPut()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'PUT',
            'https://tests.com/to/put'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->put('/to/put', TestHandler::class);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::purge
     */
    public function testPurge()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'PURGE',
            'https://tests.com/to/purge'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->purge('/to/purge', TestHandler::class);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::trace
     */
    public function testTrace()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'TRACE',
            'https://tests.com/to/trace'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->trace('/to/trace', TestHandler::class);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::head
     */
    public function testHead()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'HEAD',
            'https://tests.com/to/head'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->head('/to/head', TestHandler::class);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::any
     */
    public function testAny()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'PUT',
            'https://tests.com/to/any'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->any('/to/any', TestHandler::class);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::patch
     */
    public function testPatch()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'PATCH',
            'https://tests.com/to/patch'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->patch('/to/patch', TestHandler::class);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::get
     */
    public function testGet()
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
     * @covers ::options
     */
    public function testOptions()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'OPTIONS',
            'https://tests.com/to/options'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->options('/to/options', TestHandler::class);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::match
     */
    public function testMatch()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/to/test'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->match(['GET', 'POST', 'PUT'], '/to/test', TestHandler::class);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::pipe
     * @covers ::get
     */
    public function testRouteWithArrayOfMiddlewareThenHandler()
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
            TestHandler::class
        ]);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::pipe
     * @covers ::get
     */
    public function testRouteWithArrayOfMiddlewareButHandlerThrowException()
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
     * @covers ::pipe
     * @covers ::get
     */
    public function testRouteWithArrayOfMiddlewareButInvalidHandlerTypeThrowException()
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
            stdClass::class,
            CMiddleware::class
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->app->runAndGetResponse($server_request);
    }

    /**
     * @covers ::group
     */
    public function testGroupedRoutesFound()
    {
        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->group('/grouped/path', function (App $app) {
            $app->get('/to/get', TestHandler::class);
        });

        $this->app->get('/to/get', [
            BMiddleware::class,
            TestHandler::class
        ]);

        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/grouped/path/to/get'
        );
        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::group
     */
    public function testGroupedRoutesSkipped()
    {
        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->group('/grouped/path', function (App $app) {
            $app->get('/to/get', TestHandler::class);
        });

        $this->app->get('/to/get', [
            BMiddleware::class,
            TestHandler::class
        ]);

        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/to/get'
        );
        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(BMiddleware::class.'::process', $response->getBody()->getContents());
        $this->assertEquals('TEST', $response->getHeaderLine('X-Test'));
    }

    /**
     * @covers ::group
     */
    public function testGroupedGroupedRoutesFound()
    {
        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->group('/grouped/path', function (App $app) {
            $app->group('/to', function (App $app) {
                $app->get('/get', TestHandler::class);
            });
        });

        $this->app->get('/to/get', [
            BMiddleware::class,
            TestHandler::class
        ]);

        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/grouped/path/to/get'
        );
        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::run
     */
    public function testRun()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/to/get'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->get('/to/get', TestHandler::class);

        ob_start();
        @$this->app->run($server_request);
        $content = ob_get_clean();

        $this->assertEquals('BorschTest\Mockup\TestHandler::handle', $content);
    }
}
