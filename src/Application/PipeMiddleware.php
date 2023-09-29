<?php
/**
 * @author debuss-a
 */

namespace Borsch\Application;

use Psr\Container\{ContainerExceptionInterface, ContainerInterface, NotFoundExceptionInterface};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

/**
 * Class PipeMiddleware
 * @package Borsch\Application
 */
class PipeMiddleware implements MiddlewareInterface
{

    /** @var ContainerInterface */
    protected ContainerInterface $container;

    /**
     * PipeMiddleware constructor.
     *
     * @param string $path
     * @param string $middleware
     * @param ContainerInterface $container
     */
    public function __construct(
        protected string $path,
        protected string $middleware,
        ContainerInterface &$container
    ) {
        $this->container = &$container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (str_starts_with($request->getUri()->getPath(), $this->path)) {
            return $this->container->get($this->middleware)->process($request, $handler);
        }

        return $handler->handle($request);
    }
}
