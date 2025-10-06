<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use RuntimeException;

/** @internal */
final class ConverterHasNoParameter extends RuntimeException
{
    public function __construct(MethodDefinition|FunctionDefinition $function)
    {
        parent::__construct(
            "The value converter `$function->signature` has no parameter to convert the value to, a typed parameter is required.",
        );
    }
}
