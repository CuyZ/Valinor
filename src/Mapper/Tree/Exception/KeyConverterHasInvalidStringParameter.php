<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Type\Type;
use LogicException;

/** @internal */
final class KeyConverterHasInvalidStringParameter extends LogicException
{
    public function __construct(MethodDefinition|FunctionDefinition $method, Type $parameterType)
    {
        parent::__construct(
            "Key converter's parameter must be a string, `{$parameterType->toString()}` given for `$method->signature`.",
        );
    }
}
