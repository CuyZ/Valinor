<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use LogicException;

/** @internal */
final class ConverterHasTooManyParameters extends LogicException
{
    public function __construct(MethodDefinition|FunctionDefinition $method)
    {
        parent::__construct(
            "Converter must have at most 2 parameters, {$method->parameters->count()} given for `$method->signature`.",
        );
    }
}
