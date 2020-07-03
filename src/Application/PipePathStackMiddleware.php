<?php
/**
 * @author debuss-a
 */

namespace Borsch\Application;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplStack;

class PipePathStackMiddleware implements MiddlewareInterface, RequestHandlerInterface
{

    /** @var string */
    protected $path;

    /** @var SplStack */
    protected $stack;

    /**
     * PipePathStackMiddleware constructor.
     * @param string $path
     * @param MiddlewareInterface[] $middlewares
     */
    public function __construct(string $path, array $middlewares)
    {
        $this->path = $path;
        $this->stack = new SplStack();

        foreach ($middlewares as $middleware) {
            if (!$middleware instanceof MiddlewareInterface) {
                throw new InvalidArgumentException(sprintf(
                    'Provided middleware is not an instance of MiddlewareInterface, "%s" given...',
                    is_object($middleware) ? get_class($middleware) : gettype($middleware)
                ));
            }

            $this->stack->push($middleware);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (strpos($request->getUri()->getPath(), $this->path) === 0) {
            return $this->handle($request);
        }

        return $handler->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->stack->shift()->process($request, $this);
    }
}
