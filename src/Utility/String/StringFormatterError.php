<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\String;

use RuntimeException;
use Throwable;

/** @internal */
final class StringFormatterError extends RuntimeException
{
    public function __construct(string $body, string $message = '', ?Throwable $previous = null)
    {
        if ($message !== '') {
            $message = ": $message";
        }
        parent::__construct("Message formatter error using `$body`$message.", previous: $previous);
    }
}
