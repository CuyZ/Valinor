<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

/** @internal */
final class ShapedArrayElementDuplicatedKey extends RuntimeException implements InvalidType
{
    public function __construct(string $key)
    {
        parent::__construct("Key `$key` cannot be used several times in shaped array.");
    }
}
