<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Exception;

use CuyZ\Valinor\Type\Type;
use RuntimeException;

final class InvalidIntersectionType extends RuntimeException implements InvalidType
{
    public function __construct(Type $type)
    {
        parent::__construct(
            "Invalid intersection member `$type`, it must be a class name or an interface name.",
            1608030163
        );
    }
}
