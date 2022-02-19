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
 * Class PipeMiddleware
 * @package Borsch\Application
 */
class PipeMiddleware implements MiddlewareInterface
{

    /** @var string */
    protected $middleware;

    /** @var ContainerInterface */
    protected $container;

    /** @var string */
    protected $path;

    /**
     * PipeMiddleware constructor.
     *
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
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (strpos($request->getUri()->getPath(), $this->path) === 0) {
            return $this->container->get($this->middleware)->process($request, $handler);
        }

        return $handler->handle($request);
    }
}
