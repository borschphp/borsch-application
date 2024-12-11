<?php

namespace Borsch\Application\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class ApplicationRuntimeException extends RuntimeException
{

    public static function emptyStack(): ApplicationRuntimeException
    {
        return new self(sprintf(
            'The handler stack is empty and no %s has been returned',
            ResponseInterface::class
        ));
    }
}
