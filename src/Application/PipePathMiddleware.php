<?php
/**
 * @author debuss-a
 */

namespace Borsch\Application;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class PipePathMiddleware
 * @package Borsch\Application
 */
class PipePathMiddleware implements MiddlewareInterface
{

    /** @var string */
    protected $path;

    /** @var string */
    protected $middleware;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param string $path
     * @param string $middleware
     * @param ContainerInterface $container
     */
    public function __construct(string $path, string $middleware, ContainerInterface &$container)
    {
        $this->path = $path;
        $this->middleware = $middleware;
        $this->container = &$container;
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (strpos($request->getUri()->getPath(), $this->path) === 0) {
            $middleware = $this->container->get($this->middleware);

            return $middleware->process($request, $handler);
        }

        return $handler->handle($request);
    }
}
