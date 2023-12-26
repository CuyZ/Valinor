<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use LogicException;

/** @internal */
final class TransformerHasTooManyParameters extends LogicException
{
    public function __construct(FunctionDefinition $function)
    {
        parent::__construct(
            "Transformer must have at most 2 parameters, {$function->parameters()->count()} given for `{$function->signature()}`.",
            1695065433,
        );
    }
}
