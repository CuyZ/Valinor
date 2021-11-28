<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use RuntimeException;

final class MissingMethodArgument extends RuntimeException implements Message
{
    public function __construct(ParameterDefinition $parameter)
    {
        parent::__construct(
            "Missing argument `{$parameter->signature()}` of type `{$parameter->type()}`.",
            1629468609
        );
    }
}
