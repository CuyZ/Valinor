<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\MethodDefinition;
use RuntimeException;

final class InvalidConstructorMethodReturnType extends RuntimeException
{
    /**
     * @param class-string $expectedClass
     */
    public function __construct(MethodDefinition $method, string $expectedClass)
    {
        parent::__construct(
            "Method `{$method->signature()}` must return `$expectedClass` to be a valid constructor.",
            1638094383
        );
    }
}
