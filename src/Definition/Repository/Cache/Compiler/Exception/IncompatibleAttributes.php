<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler\Exception;

use CuyZ\Valinor\Definition\Attributes;
use LogicException;

use function get_class;

final class IncompatibleAttributes extends LogicException
{
    public function __construct(Attributes $attributes)
    {
        $class = get_class($attributes);

        parent::__construct(
            "The Attributes class of type `$class` cannot be compiled.",
            1616925611
        );
    }
}
