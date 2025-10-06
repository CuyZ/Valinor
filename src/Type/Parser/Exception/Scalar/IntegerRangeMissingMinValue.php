<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Scalar;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

/** @internal */
final class IntegerRangeMissingMinValue extends RuntimeException implements InvalidType
{
    public function __construct()
    {
        parent::__construct('Missing min value for integer range, its signature must match `int<min, max>`.');
    }
}
