<?php
/**
 * @author debuss-a
 */

namespace BorschTest\Application;

require_once __DIR__.'/../../vendor/autoload.php';

use Borsch\Application\PipePathMiddleware;
use Borsch\RequestHandler\RequestHandler;
use BorschTest\Middleware\NotFoundHandlerMiddleware;
use BorschTest\Middleware\PipedMiddleware;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class PipePathMiddlewareTest extends TestCase
{

    public function testProcessWithPath()
    {
        $request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/to/test'
        );

        $handler = new RequestHandler();
        $middleware = new PipePathMiddleware('/to', new PipedMiddleware());

        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(PipedMiddleware::class.'::process', $response->getBody()->getContents());
    }

    public function testProcessWithWrongPath()
    {
        $request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/test/with/wrong/path'
        );

        $handler = new RequestHandler();
        $handler->middleware(new NotFoundHandlerMiddleware());
        $middleware = new PipePathMiddleware('/to', new PipedMiddleware());

        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testProcessWithPathNotPlacedAtTheBeginningOrUri()
    {
        $request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/test/with/to/path'
        );

        $handler = new RequestHandler();
        $handler->middleware(new NotFoundHandlerMiddleware());
        $middleware = new PipePathMiddleware('/to', new PipedMiddleware());

        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
