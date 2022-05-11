<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\MethodDefinition;
use RuntimeException;

/** @internal */
final class InvalidConstructorMethodClassReturnType extends RuntimeException
{
    /**
     * @param class-string $expectedClass
     */
    public function __construct(MethodDefinition $method, string $expectedClass)
    {
        parent::__construct(
            "Method `{$method->signature()}` must return `$expectedClass` to be a valid constructor but returns `{$method->returnType()}`.",
            1_638_094_499
        );
    }
}
