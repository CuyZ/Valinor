<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler\Exception;

use CuyZ\Valinor\Type\Type;
use LogicException;

/** @internal */
final class TypeCannotBeCompiled extends LogicException
{
    public function __construct(Type $type)
    {
        $class = $type::class;

        parent::__construct("The type `$class` cannot be compiled.");
    }
}
