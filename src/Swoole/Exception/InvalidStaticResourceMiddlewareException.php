<?php

declare(strict_types=1);

namespace Dot\Swoole\Exception;

use function gettype;
use function get_class;
use function is_object;
use function sprintf;

class InvalidStaticResourceMiddlewareException extends InvalidArgumentException
{
    public static function forMiddlewareAtPosition($middleware, $position) : self
    {
        return new self(sprintf(
            'Static resource middleware must be callable; received middleware of type "%s" in position %s',
            is_object($middleware) ? get_class($middleware) : gettype($middleware),
            $position
        ));
    }
}
