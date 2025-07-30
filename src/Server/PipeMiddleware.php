<?php

namespace Borsch\Application\Server;

use Borsch\Application\Exception\ApplicationInvalidArgumentException;
use Borsch\Application\Factory\HandlerFactory;
use Psr\Container\{ContainerExceptionInterface, ContainerInterface, NotFoundExceptionInterface};
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class PipeMiddleware implements MiddlewareInterface
{

    protected ContainerInterface $container;

    public function __construct(
        protected string $path,
        protected string $middleware,
        protected HandlerFactory $handler_factory
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ApplicationInvalidArgumentException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (str_starts_with($request->getUri()->getPath(), $this->path)) {
            return $this->handler_factory->create($this->middleware)->process($request, $handler);
        }

        return $handler->handle($request);
    }
}
