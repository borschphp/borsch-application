<?php
/**
 * @author debuss-a
 */

namespace Borsch\Application;

use InvalidArgumentException;
use Psr\Container\{ContainerExceptionInterface, ContainerInterface, NotFoundExceptionInterface};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use RuntimeException;
use SplStack;

/**
 * Class LazyLoadingHandler
 * @package Borsch\Application
 */
class LazyLoadingHandler implements RequestHandlerInterface
{

    /** @var ContainerInterface $container */
    protected ContainerInterface $container;

    /** @var SplStack */
    protected SplStack $stack;

    /**
     * @param string|string[] $handlers
     * @param ContainerInterface $container
     */
    public function __construct(string|array $handlers, ContainerInterface &$container) {
        $this->container = &$container;

        $this->stack = new SplStack();
        foreach ((array)$handlers as $handler) {
            $this->stack->push($handler);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->stack->isEmpty()) {
            throw new RuntimeException(sprintf(
                'The handler stack is empty and no %s has been returned, this probably happened because you did not set'.
                ' a %s in your route handler stack.',
                ResponseInterface::class,
                RequestHandlerInterface::class
            ));
        }

        /** @var MiddlewareInterface|RequestHandlerInterface|mixed $handler */
        $handler = $this->container->get($this->stack->shift());

        if ($handler instanceof RequestHandlerInterface) {
            return $handler->handle($request);
        }

        if ($handler instanceof MiddlewareInterface) {
            return $handler->process($request, $this);
        }

        throw new InvalidArgumentException(sprintf(
            'Route handler must be an instance of %s or an array of %s containing a %s, "%s" provided...',
            RequestHandlerInterface::class,
            MiddlewareInterface::class,
            RequestHandlerInterface::class,
            is_object($handler) ? get_class($handler) : gettype($handler)
        ));
    }
}
