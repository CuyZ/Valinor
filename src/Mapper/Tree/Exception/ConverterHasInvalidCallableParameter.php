<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Type\Type;

/** @internal */
final class ConverterHasInvalidCallableParameter extends \LogicException
{
    public function __construct(MethodDefinition|FunctionDefinition $method, Type $parameterType)
    {
        parent::__construct(
            "Converter's second parameter must be a callable, `{$parameterType->toString()}` given for `$method->signature`.",
            1751296766,
        );
    }
}
