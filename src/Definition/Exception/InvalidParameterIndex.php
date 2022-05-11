<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use CuyZ\Valinor\Definition\Parameters;
use OutOfBoundsException;

/** @internal */
final class InvalidParameterIndex extends OutOfBoundsException
{
    public function __construct(int $index, Parameters $parameters)
    {
        $max = $parameters->count() - 1;

        parent::__construct(
            "Index $index is out of range, it should be between 0 and $max.",
            1_644_936_619
        );
    }
}
