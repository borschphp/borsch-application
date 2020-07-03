<?php
/**
 * @author debuss-a
 */

namespace BorschTest\Mockup;

use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AMiddleware
 */
class BMiddleware implements \Psr\Http\Server\MiddlewareInterface
{

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new TextResponse(__METHOD__, 200, [
            'X-Test' => 'TEST'
        ]);
    }
}