<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use LogicException;

/** @internal */
final class TransformerHasTooManyParameters extends LogicException
{
    public function __construct(MethodDefinition|FunctionDefinition $method)
    {
        parent::__construct(
            "Transformer must have at most 2 parameters, {$method->parameters->count()} given for `$method->signature`.",
        );
    }
}
