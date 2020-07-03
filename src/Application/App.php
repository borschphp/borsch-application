<?php
/**
 * @author debuss-a
 */

namespace Borsch\Application;

use Borsch\RequestHandler\Emitter;
use Borsch\Router\Route;
use Borsch\Router\RouterInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class App
 * @package Borsch\Application
 */
class App implements ApplicationInterface
{

    /** @var RequestHandlerInterface */
    protected $request_handler;

    /** @var RouterInterface */
    protected $router;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param RequestHandlerInterface $request_handler
     * @param RouterInterface $router
     * @param ContainerInterface $container
     */
    public function __construct(RequestHandlerInterface $request_handler, RouterInterface $router, ContainerInterface $container)
    {
        $this->request_handler = $request_handler;
        $this->router = $router;
        $this->container = $container;
    }

    /**
     * @param string $middleware_or_path
     * @param null|string $middleware
     */
    public function pipe(string $middleware_or_path, ?string $middleware = null): void
    {
        $middleware = $middleware ?: $middleware_or_path;
        $path = $middleware === $middleware_or_path ? '/' : $middleware_or_path;

        $middleware = $path != '/' ?
            new PipePathMiddleware($path, $this->container->get($middleware)) :
            $this->container->get($middleware);

        $this->request_handler->middleware($middleware);
    }

    /**
     * @param ServerRequestInterface $server_request
     * @return ResponseInterface
     */
    public function runAndGetResponse(ServerRequestInterface $server_request): ResponseInterface
    {
        return $this->request_handler->handle($server_request);
    }

    /**
     * @param ServerRequestInterface $server_request
     */
    public function run(ServerRequestInterface $server_request): void
    {
        $response = $this->request_handler->handle($server_request);

        $emitter = new Emitter();
        $emitter->emit($response);
    }

    /**
     * @param string $path
     * @param string $middleware
     * @param string|null $name
     */
    public function get(string $path, string $middleware, ?string $name = null): void
    {
        $this->router->addRoute(new Route(
            ['GET'],
            $path,
            $this->container->get($middleware),
            $name
        ));
    }

    /**
     * @param string $path
     * @param string $middleware
     * @param string|null $name
     */
    public function post(string $path, string $middleware, ?string $name = null): void
    {
        $this->router->addRoute(new Route(
            ['POST'],
            $path,
            $this->container->get($middleware),
            $name
        ));
    }

    /**
     * @param string $path
     * @param string $middleware
     * @param string|null $name
     */
    public function put(string $path, string $middleware, ?string $name = null): void
    {
        $this->router->addRoute(new Route(
            ['PUT'],
            $path,
            $this->container->get($middleware),
            $name
        ));
    }

    /**
     * @param string $path
     * @param string $middleware
     * @param string|null $name
     */
    public function delete(string $path, string $middleware, ?string $name = null): void
    {
        $this->router->addRoute(new Route(
            ['DELETE'],
            $path,
            $this->container->get($middleware),
            $name
        ));
    }

    /**
     * @param string $path
     * @param string $middleware
     * @param string|null $name
     */
    public function patch(string $path, string $middleware, ?string $name = null): void
    {
        $this->router->addRoute(new Route(
            ['PATCH'],
            $path,
            $this->container->get($middleware),
            $name
        ));
    }

    /**
     * @param string $path
     * @param string $middleware
     * @param string|null $name
     */
    public function head(string $path, string $middleware, ?string $name = null): void
    {
        $this->router->addRoute(new Route(
            ['HEAD'],
            $path,
            $this->container->get($middleware),
            $name
        ));
    }

    /**
     * @param string $path
     * @param string $middleware
     * @param string|null $name
     */
    public function options(string $path, string $middleware, ?string $name = null): void
    {
        $this->router->addRoute(new Route(
            ['OPTIONS'],
            $path,
            $this->container->get($middleware),
            $name
        ));
    }

    /**
     * @param string $path
     * @param string $middleware
     * @param string|null $name
     */
    public function purge(string $path, string $middleware, ?string $name = null): void
    {
        $this->router->addRoute(new Route(
            ['PURGE'],
            $path,
            $this->container->get($middleware),
            $name
        ));
    }

    /**
     * @param string $path
     * @param string $middleware
     * @param string|null $name
     */
    public function trace(string $path, string $middleware, ?string $name = null): void
    {
        $this->router->addRoute(new Route(
            ['TRACE'],
            $path,
            $this->container->get($middleware),
            $name
        ));
    }

    /**
     * @param string $path
     * @param string $middleware
     * @param string|null $name
     */
    public function connect(string $path, string $middleware, ?string $name = null): void
    {
        $this->router->addRoute(new Route(
            ['CONNECT'],
            $path,
            $this->container->get($middleware),
            $name
        ));
    }

    /**
     * @param string $path
     * @param string $middleware
     * @param string|null $name
     */
    public function any(string $path, string $middleware, ?string $name = null): void
    {
        $this->router->addRoute(new Route(
            ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS', 'PURGE', 'TRACE', 'CONNECT'],
            $path,
            $this->container->get($middleware),
            $name
        ));
    }

    /**
     * @param string[] $methods
     * @param string $path
     * @param string $middleware
     * @param string|null $name
     */
    public function match(array $methods, string $path, string $middleware, ?string $name = null): void
    {
        $this->router->addRoute(new Route(
            $methods,
            $path,
            $this->container->get($middleware),
            $name
        ));
    }
}
