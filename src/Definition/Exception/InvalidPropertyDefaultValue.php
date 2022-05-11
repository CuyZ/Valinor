<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use LogicException;
use ReflectionProperty;

/** @internal */
final class InvalidPropertyDefaultValue extends LogicException
{
    public function __construct(ReflectionProperty $reflection, Type $type)
    {
        $signature = Reflection::signature($reflection);

        parent::__construct(
            "Default value of property `$signature` is not accepted by `$type`.",
            1_629_211_093
        );
    }
}
