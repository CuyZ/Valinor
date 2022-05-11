<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use RuntimeException;

/** @internal */
final class FunctionNotFound extends RuntimeException
{
    /**
     * @param string|int $key
     */
    public function __construct($key)
    {
        parent::__construct(
            "The function `$key` was not found.",
            1_647_523_444
        );
    }
}
