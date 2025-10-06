<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Scalar;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

/** @internal */
final class ReversedValuesForIntegerRange extends RuntimeException implements InvalidType
{
    public function __construct(int $min, int $max)
    {
        parent::__construct("The min value must be less than the max for integer range `int<$min, $max>`.");
    }
}
