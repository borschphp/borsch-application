<?php
/**
 * @author debuss-a
 */

namespace Borsch\Application;

use Borsch\RequestHandler\RequestHandler;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class StackedRequestHandler
 * @package Borsch\Application
 */
class StackedRequestHandler implements \Psr\Http\Server\RequestHandlerInterface
{

    /** @var RequestHandler */
    protected $stack;

    /**
     * StackedRequestHandler constructor.
     * @param array $stack
     */
    public function __construct()
    {
        $this->stack = new RequestHandler();
    }

    /**
     * @param MiddlewareInterface|RequestHandlerInterface $handler
     */
    public function push($handler)
    {
        if (!$handler instanceof MiddlewareInterface && !$handler instanceof RequestHandlerInterface) {
            throw new InvalidArgumentException(sprintf(
                'The handler must be an instance of MiddlewareInterface or RequestHandlerInterface, %s provided...',
                gettype($handler)
            ));
        }

        if ($handler instanceof RequestHandlerInterface) {
            $handler = new class ($handler) implements MiddlewareInterface {
                /** @var RequestHandlerInterface */
                protected $handler;

                public function __construct(RequestHandlerInterface $handler)
                {
                    $this->handler = $handler;
                }

                public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
                {
                    return $this->handler->handle($request);
                }
            };
        }

        $this->stack->middleware($handler);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->stack->handle($request);
    }
}
