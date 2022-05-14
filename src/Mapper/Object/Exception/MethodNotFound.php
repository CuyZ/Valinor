<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\ClassDefinition;
use RuntimeException;

/** @internal */
final class MethodNotFound extends RuntimeException
{
    public function __construct(ClassDefinition $class, string $methodName)
    {
        parent::__construct(
            "Method `$methodName` was not found in class `{$class->name()}`.",
            1634044209
        );
    }
}
