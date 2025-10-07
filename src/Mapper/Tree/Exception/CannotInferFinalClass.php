<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use RuntimeException;

/** @internal */
final class CannotInferFinalClass extends RuntimeException
{
    /**
     * @param class-string $className
     */
    public function __construct(string $className, FunctionDefinition $function)
    {
        parent::__construct("Cannot infer final class `$className` with function `$function->signature`.");
    }
}
