<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception;

use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class InvalidIntersectionType extends RuntimeException implements InvalidType
{
    public function __construct(Type $type)
    {
        parent::__construct(
            "Invalid intersection member `{$type->toString()}`, it must be a class name or an interface name.",
            1608030163
        );
    }
}
