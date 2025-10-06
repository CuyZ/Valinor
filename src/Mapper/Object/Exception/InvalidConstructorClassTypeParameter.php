<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Type\Type;
use LogicException;

/** @internal */
final class InvalidConstructorClassTypeParameter extends LogicException
{
    public function __construct(FunctionDefinition $function, Type $type)
    {
        parent::__construct(
            "Invalid type `{$type->toString()}` for the first parameter of the constructor `{$function->signature}`, it should be of type `class-string`.",
        );
    }
}
