<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Scalar;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

/** @internal */
final class NullableMissingRightType extends RuntimeException implements InvalidType
{
    public function __construct()
    {
        parent::__construct('Missing right type for nullable type after `?`.');
    }
}
