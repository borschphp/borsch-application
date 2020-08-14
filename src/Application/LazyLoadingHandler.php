<?php
/**
 * @author debuss-a
 */

namespace Borsch\Application;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class LazyLoadingHandler
 * @package Borsch\Application
 */
class LazyLoadingHandler implements RequestHandlerInterface
{

    /** @var ContainerInterface */
    protected $container;

    /** @var string */
    protected $handler;

    /**
     * @param string $handler
     * @param ContainerInterface $container
     */
    public function __construct(string $handler, ContainerInterface &$container)
    {
        $this->container = &$container;
        $this->handler = $handler;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var RequestHandlerInterface $handler */
        $handler = $this->container->get($this->handler);

        return $handler->handle($request);
    }
}
