<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Utility\Polyfill;
use RuntimeException;

/** @internal */
final class InvalidInterfaceResolverReturnType extends RuntimeException
{
    /**
     * @param mixed $value
     */
    public function __construct(string $interfaceName, $value)
    {
        $type = Polyfill::get_debug_type($value);

        parent::__construct(
            "Invalid type `$type`; it must be the name of a class that implements `$interfaceName`.",
            1630091260
        );
    }
}
