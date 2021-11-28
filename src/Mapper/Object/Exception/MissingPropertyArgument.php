<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\PropertyDefinition;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use RuntimeException;

final class MissingPropertyArgument extends RuntimeException implements Message
{
    public function __construct(PropertyDefinition $property)
    {
        parent::__construct(
            "Missing value `{$property->signature()}` of type `{$property->type()}`.",
            1629469529
        );
    }
}
