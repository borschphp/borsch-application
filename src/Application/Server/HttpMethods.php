<?php

namespace Borsch\Application\Server;

enum HttpMethods
{
    case GET;
    case POST;
    case PUT;
    case DELETE;
    case PATCH;
    case HEAD;
    case OPTIONS;
    case PURGE;
    case TRACE;
    case CONNECT;

    /**
     * @return string[]
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'name');
    }

}
