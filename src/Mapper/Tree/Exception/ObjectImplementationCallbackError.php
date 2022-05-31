<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use Exception;
use RuntimeException;

/** @internal */
final class ObjectImplementationCallbackError extends RuntimeException
{
    private Exception $original;

    public function __construct(string $name, Exception $original)
    {
        $this->original = $original;

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
