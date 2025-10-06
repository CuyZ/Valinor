<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Exception;

use CuyZ\Valinor\Definition\MethodDefinition;
use LogicException;

/** @internal */
final class KeyTransformerParameterInvalidType extends LogicException
{
    public function __construct(MethodDefinition $method)
    {
        parent::__construct(
            "Key transformer parameter must be a string, {$method->parameters->at(0)->type->toString()} given for `$method->signature`.",
        );
    }
}
