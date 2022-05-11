<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler\Exception;

use CuyZ\Valinor\Type\Type;
use LogicException;

use function get_class;

/** @internal */
final class TypeCannotBeCompiled extends LogicException
{
    public function __construct(Type $type)
    {
        $class = get_class($type);

        parent::__construct(
            "The type `$class` cannot be compiled.",
            1_616_926_126
        );
    }
}
