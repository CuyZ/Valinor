<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use LogicException;

/** @internal */
final class InvalidConstructorReturnType extends LogicException
{
    public function __construct(FunctionDefinition $function)
    {
        parent::__construct(
            "Invalid return type `{$function->returnType()->toString()}` for constructor `{$function->signature()}`, it must be a valid class name.",
            1659446121
        );
    }
}
