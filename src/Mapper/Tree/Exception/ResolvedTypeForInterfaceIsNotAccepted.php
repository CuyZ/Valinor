<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Type\Types\ClassType;
use RuntimeException;

/** @internal */
final class ResolvedTypeForInterfaceIsNotAccepted extends RuntimeException
{
    public function __construct(string $interfaceName, ClassType $type)
    {
        parent::__construct(
            "The implementation `$type` is not accepted by the interface `$interfaceName`.",
            1_618_049_487
        );
    }
}
