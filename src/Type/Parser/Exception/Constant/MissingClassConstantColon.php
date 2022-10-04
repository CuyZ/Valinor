<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Constant;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

/** @internal */
final class MissingClassConstantColon extends RuntimeException implements InvalidType
{
    /**
     * @param class-string $className
     */
    public function __construct(string $className, string $case)
    {
        if ($case === ':') {
            $case = '?';
        }

        parent::__construct(
            "Missing second colon symbol for class constant `$className::$case`.",
            1652189143
        );
    }
}
