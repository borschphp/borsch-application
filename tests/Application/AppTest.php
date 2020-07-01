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
use BorschTest\Mockup\BMiddleware;
use BorschTest\Mockup\TestHandler;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class AppTest extends TestCase
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

        $this->app = new App(
            new RequestHandler(),
            $container->get(RouterInterface::class),
            $container
        );
    }

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

    public function testStackedHandlersGetFinalResponse()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/to/test'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->get( '/to/test', [
            AMiddleware::class,
            TestHandler::class
        ]);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(TestHandler::class.'::handle', $response->getBody()->getContents());
    }

    public function testStackedHandlersGetMiddlewareResponse()
    {
        $server_request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/to/test'
        );

        $this->app->pipe(RouteMiddleware::class);
        $this->app->pipe(DispatchMiddleware::class);
        $this->app->pipe(NotFoundHandlerMiddleware::class);

        $this->app->get( '/to/test', [
            AMiddleware::class,
            BMiddleware::class,
            TestHandler::class
        ]);

        $response = $this->app->runAndGetResponse($server_request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertEquals(BMiddleware::class.'::process', $response->getBody()->getContents());
        $this->assertEquals('TEST', $response->getHeaderLine('X-Test'));
    }
}
