<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use LogicException;

/** @internal */
final class TransformerHasNoParameter extends LogicException
{
    public function __construct(FunctionDefinition $function)
    {
        parent::__construct(
            "Transformer must have at least one parameter, none given for `{$function->signature()}`.",
            1695064946,
        );
    }
}
