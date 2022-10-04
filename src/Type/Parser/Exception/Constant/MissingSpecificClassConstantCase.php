<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception\Constant;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use RuntimeException;

/** @internal */
final class MissingSpecificClassConstantCase extends RuntimeException implements InvalidType
{
    /**
     * @param class-string $className
     */
    public function __construct(string $className)
    {
        parent::__construct(
            "Missing specific case for class constant `$className::?` (cannot be `*`).",
            1664904636
        );
    }
}
