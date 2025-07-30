<?php

namespace Borsch\Application\Server;

use Borsch\Application\Exception\ApplicationRuntimeException;
use Borsch\Application\Factory\HandlerFactory;
use InvalidArgumentException;
use Psr\Container\{ContainerExceptionInterface, NotFoundExceptionInterface};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{RequestHandlerInterface};
use SplStack;

class LazyLoadingHandler implements RequestHandlerInterface
{

    protected HandlerFactory $handler_factory;

    /** @var SplStack<string> */
    protected SplStack $stack;

    /**
     * @param string|string[] $handlers
     * @param HandlerFactory $handler_factory
     */
    public function __construct(string|array $handlers, HandlerFactory $handler_factory) {
        $this->handler_factory = $handler_factory;

        $this->stack = new SplStack();
        foreach ((array)$handlers as $handler) {
            $this->stack->push($handler);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidArgumentException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->stack->isEmpty()) {
            throw ApplicationRuntimeException::emptyStack();
        }

        $fqcn = $this->stack->shift();
        $handler = $this->handler_factory->create($fqcn);

        if ($handler instanceof RequestHandlerInterface) {
            return $handler->handle($request);
        }

        // MiddlewareInterface
        return $handler->process($request, $this);
    }
}
