<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Scalar;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use RuntimeException;

/** @internal */
final class IntegerRangeInvalidMaxValue extends RuntimeException implements InvalidType
{
    public function __construct(IntegerValueType $min, Type $type)
    {
        parent::__construct(
            "Invalid type `{$type->toString()}` for max value of integer range `int<{$min->value()}, ?>`, it must be either `max` or an integer value."
        );
    }
}
