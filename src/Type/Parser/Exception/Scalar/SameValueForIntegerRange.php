<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Scalar;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

/** @internal */
final class SameValueForIntegerRange extends RuntimeException implements InvalidType
{
    public function __construct(int $value)
    {
        parent::__construct("The min and max values for integer range must be different, `$value` was given.");
    }
}
