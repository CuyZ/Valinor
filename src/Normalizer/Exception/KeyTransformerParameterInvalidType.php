<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use LogicException;

/** @internal */
final class KeyTransformerParameterInvalidType extends LogicException
{
    public function __construct(FunctionDefinition $function)
    {
        parent::__construct(
            "Key transformer parameter must be a string, {$function->parameters()->at(0)->type()->toString()} given for `{$function->signature()}`.",
            1701706316,
        );
    }
}
