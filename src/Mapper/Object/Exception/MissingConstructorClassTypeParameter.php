<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use LogicException;

/** @internal */
final class MissingConstructorClassTypeParameter extends LogicException
{
    public function __construct(FunctionDefinition $function)
    {
        parent::__construct(
            "Missing first parameter of type `class-string` for the constructor `$function->signature`.",
        );
    }
}
