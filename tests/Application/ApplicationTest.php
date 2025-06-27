<?php

namespace BorschTest\Application;

require_once __DIR__.'/../../vendor/autoload.php';

use Borsch\Application\{Application, ApplicationInterface};
use Borsch\Container\Container;
use Borsch\RequestHandler\RequestHandler;
use Borsch\Router\{FastRouteRouter, Route, RouterInterface};
use BorschTest\Middleware\{DispatchMiddleware, NotFoundHandlerMiddleware, PipedMiddleware, RouteMiddleware};
use BorschTest\Mockup\{AMiddleware, BMiddleware, CMiddleware, TestHandler};
use InvalidArgumentException;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use stdClass;
use TypeError;

/**
 * @coversDefaultClass Application
 * @covers Application::__construct
 * @covers Application::runAndGetResponse
 * @covers Application::run
 * @uses Application
 * @uses \Borsch\Application\PipePathMiddleware
 * @uses \Borsch\Application\Server\PipeMiddleware
 * @uses \Borsch\Application\Server\LazyLoadingHandler
 * @uses \Borsch\RequestHandler\Emitter
 * @uses RequestHandler
 */
class ApplicationTest extends TestCase
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
        $container->set(stdClass::class, function () {
            return new stdClass();
        });
        
        $this->application = new Application(new RequestHandler(), $container->get(RouterInterface::class), $container);
    }

    /**
     * @covers ::__construct
     */
    public function test__construct()
    {
        $this->assertInstanceOf(ApplicationInterface::class, $this->application);
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

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->delete('/to/delete', TestHandler::class);

        $response = $this->application->respond($server_request);

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

        $this->application->pipe('/to', PipedMiddleware::class);

        $this->application->delete('/to/delete', TestHandler::class);

        $response = $this->application->respond($server_request);

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

        $this->application->pipe( '/to/', [
            AMiddleware::class,
            BMiddleware::class
        ]);

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $response = $this->application->respond($server_request);

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

        $this->application->pipe([
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

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->delete('/to/delete', TestHandler::class);

        $response = $this->application->respond($server_request);

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

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->connect('/to/connect', TestHandler::class);

        $response = $this->application->respond($server_request);

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

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->post('/to/post', TestHandler::class);

        $response = $this->application->respond($server_request);

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

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->put('/to/put', TestHandler::class);

        $response = $this->application->respond($server_request);

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

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->purge('/to/purge', TestHandler::class);

        $response = $this->application->respond($server_request);

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

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->trace('/to/trace', TestHandler::class);

        $response = $this->application->respond($server_request);

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

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->head('/to/head', TestHandler::class);

        $response = $this->application->respond($server_request);

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

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->any('/to/any', TestHandler::class);

        $response = $this->application->respond($server_request);

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

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->patch('/to/patch', TestHandler::class);

        $response = $this->application->respond($server_request);

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
     * @covers ::options
     */
    public function testOptions()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'OPTIONS',
            'https://tests.com/to/options'
        );

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->options('/to/options', TestHandler::class);

        $response = $this->application->respond($server_request);

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

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->match(['GET', 'POST', 'PUT'], '/to/test', TestHandler::class);

        $response = $this->application->respond($server_request);

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

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->get('/to/get', [
            AMiddleware::class,
            CMiddleware::class,
            TestHandler::class
        ]);

        $response = $this->application->respond($server_request);

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
     * @covers ::pipe
     * @covers ::get
     */
    public function testRouteWithArrayOfMiddlewareButInvalidHandlerTypeThrowException()
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
            stdClass::class,
            CMiddleware::class
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->application->respond($server_request);
    }

    /**
     * @covers ::group
     */
    public function testGroupedRoutesFound()
    {
        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->group('/grouped/path', function (Application $app) {
            $app->get('/to/get', TestHandler::class);
            $app->post('/to/post', TestHandler::class);
        });

        $this->application->get('/to/get', [
            BMiddleware::class,
            TestHandler::class
        ]);

        // Test GET route
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/grouped/path/to/get'
        );
        $response = $this->application->respond($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::group
     */
    public function testGroupedRoutesFoundPost()
    {
        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->group('/grouped/path', function (Application $app) {
            $app->get('/to/get', TestHandler::class);
            $app->post('/to/post', TestHandler::class);
        });

        $this->application->get('/to/get', [
            BMiddleware::class,
            TestHandler::class
        ]);

        // Test POST route
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'POST',
            'https://tests.com/grouped/path/to/post'
        );
        $response = $this->application->respond($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::group
     */
    public function testGroupedRoutesSkipped()
    {
        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->group('/grouped/path', function (Application $app) {
            $app->get('/to/get', TestHandler::class);
        });

        $this->application->get('/to/get', [
            BMiddleware::class,
            TestHandler::class
        ]);

        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/to/get'
        );
        $response = $this->application->respond($server_request);

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
        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->group('/grouped/path', function (Application $app) {
            $app->group('/to', function (Application $app) {
                $app->get('/get', TestHandler::class);
            });
        });

        $this->application->get('/to/get', [
            BMiddleware::class,
            TestHandler::class
        ]);

        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/grouped/path/to/get'
        );
        $response = $this->application->respond($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    /**
     * @covers ::respond
     */
    public function testRespond()
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
        $this->assertEquals(200, $response->getStatusCode());
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

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->get('/to/get', TestHandler::class);

        ob_start();
        @$this->application->run($server_request);
        $content = ob_get_clean();

        $this->assertEquals('BorschTest\Mockup\TestHandler::handle', $content);
    }

    public function testAddRoute00()
    {
        $routes = [
            new Route(['GET'], '/a/b/c', new TestHandler()),
            new Route(['GET'], '/z/y/x', new TestHandler()),
            new Route(['GET'], '/air/plane', new TestHandler()),
            new Route(['GET'], '/steam/bot', new TestHandler()),
            new Route(['GET'], '/m/n/o', new TestHandler()),
        ];

        $this->application->pipe(RouteMiddleware::class);
        $this->application->pipe(DispatchMiddleware::class);
        $this->application->pipe(NotFoundHandlerMiddleware::class);

        $this->application->addRoutes($routes);

        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/air/plane'
        );

        $response = $this->application->respond($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }
}
