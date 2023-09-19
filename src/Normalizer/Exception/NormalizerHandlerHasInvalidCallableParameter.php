<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Type\Type;
use LogicException;

/** @internal */
final class NormalizerHandlerHasInvalidCallableParameter extends LogicException
{
    public function __construct(FunctionDefinition $function, Type $parameterType)
    {
        parent::__construct(
            "Normalizer handler's second parameter must be a callable, `{$parameterType->toString()}` given for `{$function->signature()}`.",
            1695065710,
        );
    }
}
