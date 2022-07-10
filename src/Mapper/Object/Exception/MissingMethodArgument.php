<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\ParameterDefinition;
use RuntimeException;

/** @internal */
final class MissingMethodArgument extends RuntimeException
{
    public function __construct(ParameterDefinition $parameter)
    {
        parent::__construct(
            "Missing argument `{$parameter->signature()}` of type `{$parameter->type()->toString()}`.",
            1629468609
        );
    }
}
