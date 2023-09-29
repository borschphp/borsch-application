<?php
/**
 * @author debuss-a
 */

namespace Borsch\Application;

use Borsch\RequestHandler\{ApplicationRequestHandlerInterface, Emitter};
use Borsch\Router\{Route, RouterInterface};
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class App
 * @package Borsch\Application
 */
class App implements ApplicationInterface
{

    /** @var string */
    protected string $start_path = '';

    /**
     * @param ApplicationRequestHandlerInterface $request_handler
     * @param RouterInterface $router
     * @param ContainerInterface $container
     */
    public function __construct(
        protected ApplicationRequestHandlerInterface $request_handler,
        protected RouterInterface $router,
        protected ContainerInterface $container
    ) {}

    /**
     * @param string $middleware_or_path
     * @param null|string|string[] $middlewares
     */
    public function pipe(string $middleware_or_path, $middlewares = null): void
    {
        $middlewares = $middlewares ?: $middleware_or_path;
        $path = $middlewares === $middleware_or_path ? '/' : $middleware_or_path;

        foreach ((array)$middlewares as $middleware) {
            $this->request_handler->middleware(
                new PipeMiddleware($path, $middleware, $this->container)
            );
        }
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
     * @param string $start_path
     * @param callable $proxy
     */
    public function group(string $start_path, callable $proxy): void
    {
        $prev_start_path = $this->start_path;
        $this->start_path .= $start_path;

        call_user_func($proxy, $this);

        $this->start_path = $prev_start_path;
    }

    /**
     * @param string $path
     * @param string|string[] $handler
     * @param string|null $name
     */
    public function get(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::GET, $path, $handler, $name);
    }

    /**
     * @param string $path
     * @param string|string[] $handler
     * @param string|null $name
     */
    public function post(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::POST, $path, $handler, $name);
    }

    /**
     * @param string $path
     * @param string|string[] $handler
     * @param string|null $name
     */
    public function put(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::PUT, $path, $handler, $name);
    }

    /**
     * @param string $path
     * @param string|string[] $handler
     * @param string|null $name
     */
    public function delete(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::DELETE, $path, $handler, $name);
    }

    /**
     * @param string $path
     * @param string|string[] $handler
     * @param string|null $name
     */
    public function patch(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::PATCH, $path, $handler, $name);
    }

    /**
     * @param string $path
     * @param string|string[] $handler
     * @param string|null $name
     */
    public function head(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::HEAD, $path, $handler, $name);
    }

    /**
     * @param string $path
     * @param string|string[] $handler
     * @param string|null $name
     */
    public function options(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::OPTIONS, $path, $handler, $name);
    }

    /**
     * @param string $path
     * @param string|string[] $handler
     * @param string|null $name
     */
    public function purge(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::PURGE, $path, $handler, $name);
    }

    /**
     * @param string $path
     * @param string|string[] $handler
     * @param string|null $name
     */
    public function trace(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::TRACE, $path, $handler, $name);
    }

    /**
     * @param string $path
     * @param string|string[] $handler
     * @param string|null $name
     */
    public function connect(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::CONNECT, $path, $handler, $name);
    }

    /**
     * @param string $path
     * @param string|string[] $handler
     * @param string|null $name
     */
    public function any(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::toArray(), $path, $handler, $name);
    }

    /**
     * @param HttpMethods|HttpMethods[]|string $methods
     * @param string $path
     * @param string|string[] $handler
     * @param string|null $name
     */
    public function match(HttpMethods|array|string $methods, string $path, string|array $handler, ?string $name = null): void
    {
        $methods = array_map(
            fn($method) => $method instanceof HttpMethods ?
                $method->name :
                $method,
            (array)$methods
        );

        $this->router->addRoute(new Route(
            $methods,
            $this->start_path.$path,
            new LazyLoadingHandler($handler, $this->container),
            $name
        ));
    }
}
