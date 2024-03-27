<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Mapper\Tree\Exception\UnresolvableShellType;
use LogicException;

/** @internal */
final class TypeErrorDuringArgumentsMapping extends LogicException
{
    public function __construct(FunctionDefinition $function, UnresolvableShellType $exception)
    {
        parent::__construct(
            "Could not map arguments of `$function->signature`: {$exception->getMessage()}",
            1711534351,
            $exception,
        );
    }
}
