<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use LogicException;

/** @internal */
final class KeyConverterHasTooManyParameters extends LogicException
{
    public function __construct(MethodDefinition|FunctionDefinition $method)
    {
        parent::__construct(
            "Key converter must have only one parameter, {$method->parameters->count()} given for `$method->signature`.",
        );
    }
}
