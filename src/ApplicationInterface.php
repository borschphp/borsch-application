<?php

namespace Borsch\Application;

use Borsch\Router\RouteInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface defining a Borsch Application.
 */
interface ApplicationInterface
{

    /**
     * Pipe middleware to the pipeline.
     * If two parameters are present, the first one must be a string representing a path to segregate
     * with the second one.
     * The middleware will be fetched from the container, therefore only give the container identifier.
     * An array of middleware can be provided for the second parameter.
     *
     * @param string $middleware_or_path
     * @param string|string[] $middlewares
     */
    public function pipe(string $middleware_or_path, string|array|null $middlewares = null): void;

    /**
     * Respond with a ResponseInterface to a server request.
     *
     * @param ServerRequestInterface $server_request
     * @return ResponseInterface
     */
    public function respond(ServerRequestInterface $server_request): ResponseInterface;

    /**
     * Emit the ResponseInterface to a client.
     *
     * @param ResponseInterface $response
     */
    public function emit(ResponseInterface $response): void;

    /**
     * Run the application (e.g.: gets the response then emits it).
     *
     * @param ServerRequestInterface $server_request
     */
    public function run(ServerRequestInterface $server_request): void;

    /**
     * Add a GET Route to the application router instance.
     *
     * @param string $path
     * @param string $handler
     * @param string|null $name
     */
    public function get(string $path, string $handler, ?string $name = null): void;

    /**
     * Add a POST Route to the application router instance.
     *
     * @param string $path
     * @param string $handler
     * @param string|null $name
     */
    public function post(string $path, string $handler, ?string $name = null): void;

    /**
     * Add a PUT Route to the application router instance.
     *
     * @param string $path
     * @param string $handler
     * @param string|null $name
     */
    public function put(string $path, string $handler, ?string $name = null): void;

    /**
     * Add a DELETE Route to the application router instance.
     *
     * @param string $path
     * @param string $handler
     * @param string|null $name
     */
    public function delete(string $path, string $handler, ?string $name = null): void;

    /**
     * Add a PATH Route to the application router instance.
     *
     * @param string $path
     * @param string $handler
     * @param string|null $name
     */
    public function patch(string $path, string $handler, ?string $name = null): void;

    /**
     * Add a HEAD Route to the application router instance.
     *
     * @param string $path
     * @param string $handler
     * @param string|null $name
     */
    public function head(string $path, string $handler, ?string $name = null): void;

    /**
     * Add an OPTIONS Route to the application router instance.
     *
     * @param string $path
     * @param string $handler
     * @param string|null $name
     */
    public function options(string $path, string $handler, ?string $name = null): void;

    /**
     * Add an PURGE Route to the application router instance.
     *
     * @param string $path
     * @param string $handler
     * @param string|null $name
     */
    public function purge(string $path, string $handler, ?string $name = null): void;

    /**
     * Add an TRACE Route to the application router instance.
     *
     * @param string $path
     * @param string $handler
     * @param string|null $name
     */
    public function trace(string $path, string $handler, ?string $name = null): void;

    /**
     * Add an CONNECT Route to the application router instance.
     *
     * @param string $path
     * @param string $handler
     * @param string|null $name
     */
    public function connect(string $path, string $handler, ?string $name = null): void;

    /**
     * Add a Route to all methods to the application router instance.
     *
     * @param string $path
     * @param string $handler
     * @param string|null $name
     */
    public function any(string $path, string $handler, ?string $name = null): void;

    /**
     * Add multiple routes at once to the application router instance.
     *
     * @param RouteInterface[] $routes
     */
    public function addRoutes(array $routes): void;
}
