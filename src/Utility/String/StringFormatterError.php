<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\String;

use RuntimeException;

/** @internal */
final class StringFormatterError extends RuntimeException
{
    public function __construct(string $body, string $message)
    {
        parent::__construct("Message formatter error using `$body`: $message.", 1652901203);
    }
}
