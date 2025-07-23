<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Iterable;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class InvalidArrayKey extends RuntimeException implements InvalidType
{
    public function __construct(Type $keyType)
    {
        parent::__construct(
            "Invalid array key type `{$keyType->toString()}`, it must be a valid string or integer.",
            1604335007
        );
    }
}
