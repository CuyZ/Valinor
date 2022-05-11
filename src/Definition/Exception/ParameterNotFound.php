<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use LogicException;

/** @internal */
final class ParameterNotFound extends LogicException
{
    public function __construct(string $parameter)
    {
        parent::__construct(
            "The parameter `$parameter` does not exist.",
            1_514_302_629
        );
    }
}
