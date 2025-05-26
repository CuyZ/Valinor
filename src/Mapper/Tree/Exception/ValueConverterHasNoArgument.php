<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Definition\FunctionDefinition;
use RuntimeException;

/** @api */
final class ValueConverterHasNoArgument extends RuntimeException
{
    public function __construct(FunctionDefinition $function)
    {
        parent::__construct(
            "The value converter `$function->signature` has no argument to convert the value to, a typed argument is required.",
            1746449489,
        );
    }
}
