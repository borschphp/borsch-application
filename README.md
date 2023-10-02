# Borsch - Application

Borsch Framework application wrapper.  

This package is part of the Borsch Framework.

## Installation

Via [composer](https://getcomposer.org/) :

`composer require borschphp/application`

## Usage

```php
$container = new Container();
$container->set(PipePathMiddleware::class);
$container->set(RouteMiddleware::class);
$container->set(DispatchMiddleware::class);
$container->set(NotFoundHandlerMiddleware::class);
$container->set(TestHandler::class);
$container->set(FastRouteRouter::class);
$container->set(RouterInterface::class, FastRouteRouter::class)->cache(true);

$app = new App(
    new RequestHandler(),
    $container->get(RouterInterface::class),
    $container
);

$app->pipe(RouteMiddleware::class);
$app->pipe(DispatchMiddleware::class);
$app->pipe(NotFoundHandlerMiddleware::class);

$app->get('/a/get/path', TestHandler::class);

$app->run(ServerRequestFactory::fromGlobals());
```

## License

The package is licensed under the MIT license. See [License File](https://github.com/borschphp/borsch-application/blob/master/LICENSE.md) for more information.