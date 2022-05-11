<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use LogicException;
use ReflectionParameter;

/** @internal */
final class InvalidParameterDefaultValue extends LogicException
{
    public function __construct(ReflectionParameter $reflection, Type $type)
    {
        $signature = Reflection::signature($reflection);

        parent::__construct(
            "Default value of parameter `$signature` is not accepted by `$type`.",
            1_629_210_903
        );
    }
}
