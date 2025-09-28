<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Mapper\Tree\Exception\UnresolvableShellType;
use LogicException;

use function lcfirst;

/** @internal */
final class TypeErrorDuringArgumentsMapping extends LogicException
{
    public function __construct(FunctionDefinition $function, UnresolvableShellType $exception)
    {
        parent::__construct(
            "Could not map arguments of `$function->signature`: " . lcfirst($exception->getMessage()),
            previous: $exception,
        );
    }
}
