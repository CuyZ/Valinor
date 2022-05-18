<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\String;

use RuntimeException;

/** @internal */
final class StringFormatterError extends RuntimeException
{
    public function __construct(string $body)
    {
        parent::__construct("Message formatter error using `$body`.", 1652901203);
    }
}
