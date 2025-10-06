<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use Exception;
use RuntimeException;

/** @internal */
final class ObjectImplementationCallbackError extends RuntimeException
{
    // @phpstan-ignore constructor.missingParentCall
    public function __construct(private Exception $original) {}

    public function original(): Exception
    {
        return $this->original;
    }
}
