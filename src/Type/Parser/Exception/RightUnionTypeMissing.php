<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception;

use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class RightUnionTypeMissing extends RuntimeException implements InvalidType
{
    public function __construct(Type $type)
    {
        parent::__construct(
            "Right type is missing for union `{$type->toString()}|?`.",
            1631294715
        );
    }
}
