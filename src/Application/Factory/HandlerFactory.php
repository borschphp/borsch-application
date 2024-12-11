<?php

namespace Borsch\Application\Factory;

use Borsch\Application\Exception\ApplicationInvalidArgumentException;
use Psr\Container\{ContainerExceptionInterface, ContainerInterface, NotFoundExceptionInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};

class HandlerFactory
{

    public function __construct(
        protected ContainerInterface $container
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ApplicationInvalidArgumentException
     */
    public function create(string $handler_fqcn): RequestHandlerInterface|MiddlewareInterface
    {
        $handler = $this->container->get($handler_fqcn);
        if (!$handler instanceof RequestHandlerInterface && !$handler instanceof MiddlewareInterface) {
            throw ApplicationInvalidArgumentException::invalidHandler($handler_fqcn);
        }

        return $handler;
    }
}
