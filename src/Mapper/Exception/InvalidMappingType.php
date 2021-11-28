<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Exception;

use CuyZ\Valinor\Type\Type;
use RuntimeException;

final class InvalidMappingType extends RuntimeException
{
    public function __construct(Type $type)
    {
        parent::__construct(
            "Can not map type `$type`, it must be an object type.",
            1630959731
        );
    }
}
