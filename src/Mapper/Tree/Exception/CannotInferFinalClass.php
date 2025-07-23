<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Type\ClassType;
use RuntimeException;

/** @internal */
final class CannotInferFinalClass extends RuntimeException
{
    public function __construct(ClassType $class, FunctionDefinition $function)
    {
        parent::__construct(
            "Cannot infer final class `{$class->className()}` with function `$function->signature`.",
            1671468163
        );
    }
}
