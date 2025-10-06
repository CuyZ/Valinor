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
        if ($function->returnType instanceof UnresolvableType) {
            $message = $function->returnType->message();
        } else {
            $message = "Invalid return type `{$function->returnType->toString()}` for constructor `{$function->signature}`, it must be a valid class name.";
        }

        parent::__construct($message);
    }
}
