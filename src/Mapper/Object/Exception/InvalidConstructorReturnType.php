<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use LogicException;

/** @internal */
final class InvalidConstructorReturnType extends LogicException
{
    public function __construct(FunctionDefinition $function)
    {
        $returnType = $function->returnType();

        $message = $returnType instanceof UnresolvableType
            ? $returnType->getMessage()
            : "Invalid return type `{$returnType->toString()}` for constructor `{$function->signature()}`, it must be a valid class name.";

        parent::__construct($message, 1659446121);
    }
}
