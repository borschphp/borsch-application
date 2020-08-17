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

    /**
     * PipeMiddleware constructor.
     *
     * @param string $middleware
     * @param ContainerInterface $container
     */
    public function __construct(string $middleware, ContainerInterface &$container)
    {
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
        return $this->container->get($this->middleware)->process($request, $handler);
    }
}
