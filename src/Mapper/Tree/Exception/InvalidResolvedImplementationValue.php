<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Utility\ValueDumper;
use RuntimeException;

/** @internal */
final class InvalidResolvedImplementationValue extends RuntimeException
{
    /**
     * @param mixed $value
     */
    public function __construct(string $name, $value)
    {
        $value = ValueDumper::dump($value);

        parent::__construct(
            "Invalid value $value, expected a subtype of `$name`.",
            1630091260
        );
    }
}
