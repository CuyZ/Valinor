<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class InvalidInterfaceResolverReturnType extends RuntimeException
{
    /**
     * @param mixed $value
     */
    public function __construct(string $interfaceName, $value)
    {
        $value = ValueDumper::dump($value);

        parent::__construct(
            "Invalid value $value; it must be the name of a class that implements `$interfaceName`.",
            1630091260
        );
    }
}
