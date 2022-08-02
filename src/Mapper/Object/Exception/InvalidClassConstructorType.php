<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class InvalidClassConstructorType extends RuntimeException
{
    public function __construct(FunctionDefinition $function, Type $type)
    {
        parent::__construct(
            "Invalid type `{$type->toString()}` handled by constructor `{$function->signature()}`. It must be a valid class name.",
            1659446121
        );
    }
}
