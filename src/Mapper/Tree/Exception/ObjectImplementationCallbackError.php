<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use Exception;
use RuntimeException;

/** @internal */
final class ObjectImplementationCallbackError extends RuntimeException
{
    public function __construct(string $name, private Exception $original)
    {
        parent::__construct(
            "Error thrown when trying to get implementation of `$name`: " . $original->getMessage(),
            1653983061,
            $original
        );
    }

    public function original(): Exception
    {
        return $this->original;
    }
}
