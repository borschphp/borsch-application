<?php

namespace Borsch\Application\Exception;

use InvalidArgumentException;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApplicationInvalidArgumentException extends InvalidArgumentException
{

    public static function invalidHandler(string $handler_fqcn): ApplicationInvalidArgumentException
    {
        return new self(sprintf(
            'The handler "%s" must implement %s or %s',
            $handler_fqcn,
            RequestHandlerInterface::class,
            MiddlewareInterface::class
        ));
    }
}