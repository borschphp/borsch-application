<?php

namespace Borsch\Application;

use Borsch\Application\Factory\HandlerFactory;
use Borsch\RequestHandler\{Emitter, RequestHandlerInterface};
use Borsch\Router\{Route, RouterInterface};
use Borsch\Application\Server\{LazyLoadingHandler};
use Borsch\Application\Server\HttpMethods;
use Borsch\Application\Server\PipeMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};

class Application implements ApplicationInterface
{

    protected HandlerFactory $handler_factory;
    protected string $start_path = '';

    public function __construct(
        protected RequestHandlerInterface $request_handler,
        protected RouterInterface         $router,
        protected ContainerInterface      $container
    ) {
        $this->handler_factory = new HandlerFactory($container);
    }

    /**
     * @param null|string|string[] $middlewares
     */
    public function pipe(string $middleware_or_path, null|string|array $middlewares = null): void
    {
        $middlewares = $middlewares ?: $middleware_or_path;
        $path = $middlewares === $middleware_or_path ? '/' : $middleware_or_path;

        foreach ((array)$middlewares as $middleware) {
            $this->request_handler->middleware(
                new PipeMiddleware($path, $middleware, $this->handler_factory)
            );
        }
    }

    public function respond(ServerRequestInterface $server_request): ResponseInterface
    {
        return $this->request_handler->handle($server_request);
    }

    public function emit(ResponseInterface $response): void
    {
        $emitter = new Emitter();
        $emitter->emit($response);
    }

    public function run(ServerRequestInterface $server_request): void
    {
        $this->emit($this->respond($server_request));
    }

    public function group(string $start_path, callable $proxy): void
    {
        $prev_start_path = $this->start_path;
        $this->start_path .= $start_path;

        call_user_func($proxy, $this);

        $this->start_path = $prev_start_path;
    }

    /**
     * @param string|string[] $handler
     */
    public function get(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::GET, $path, $handler, $name);
    }

    /**
     * @param string|string[] $handler
     */
    public function post(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::POST, $path, $handler, $name);
    }

    /**
     * @param string|string[] $handler
     */
    public function put(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::PUT, $path, $handler, $name);
    }

    /**
     * @param string|string[] $handler
     */
    public function delete(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::DELETE, $path, $handler, $name);
    }

    /**
     * @param string|string[] $handler
     */
    public function patch(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::PATCH, $path, $handler, $name);
    }

    /**
     * @param string|string[] $handler
     */
    public function head(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::HEAD, $path, $handler, $name);
    }

    /**
     * @param string|string[] $handler
     */
    public function options(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::OPTIONS, $path, $handler, $name);
    }

    /**
     * @param string|string[] $handler
     */
    public function purge(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::PURGE, $path, $handler, $name);
    }

    /**
     * @param string|string[] $handler
     */
    public function trace(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::TRACE, $path, $handler, $name);
    }

    /**
     * @param string|string[] $handler
     */
    public function connect(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::CONNECT, $path, $handler, $name);
    }

    /**
     * @param string|string[] $handler
     */
    public function any(string $path, $handler, ?string $name = null): void
    {
        $this->match(HttpMethods::toArray(), $path, $handler, $name);
    }

    /**
     * @param HttpMethods|HttpMethods[]|string|string[] $methods
     * @param string|string[] $handler
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
            new LazyLoadingHandler($handler, $this->handler_factory),
            $name
        ));
    }

    public function addRoutes(array $routes): void
    {
        foreach ($routes as $route) {
            $this->router->addRoute($route);
        }
    }
}
