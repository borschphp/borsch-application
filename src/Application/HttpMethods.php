<?php
/**
 * @author debuss-a
 */

namespace Borsch\Application;

/**
 * Enum HttpMethods
 * @package Borsch\Application
 */
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
