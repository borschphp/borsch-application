<?php
/**
 * @author debuss-a
 */

namespace BorschTest\Application;

use Borsch\Application\PipePathStackMiddleware;
use Borsch\RequestHandler\RequestHandler;
use BorschTest\Mockup\AMiddleware;
use BorschTest\Mockup\BMiddleware;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class PipePathStackMiddlewareTest extends TestCase
{

    public function testProcessWithPath()
    {
        $request = (new ServerRequestFactory())->createServerRequest(
            'GET',
            'https://tests.com/to/test'
        );

        $handler = new RequestHandler();
        $middleware = new PipePathStackMiddleware('/to', [
            new AMiddleware(),
            new BMiddleware()
        ]);

        $response = $middleware->process($request, $handler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(BMiddleware::class.'::process', $response->getBody()->getContents());
        $this->assertEquals('TEST', $response->getHeaderLine('X-Test'));
    }
}
