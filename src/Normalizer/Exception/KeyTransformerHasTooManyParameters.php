<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use LogicException;

/** @internal */
final class KeyTransformerHasTooManyParameters extends LogicException
{
    public function __construct(FunctionDefinition $function)
    {
        parent::__construct(
            "Key transformer must have at most 1 parameter, {$function->parameters()->count()} given for `{$function->signature()}`.",
            1701701102,
        );
    }
}
