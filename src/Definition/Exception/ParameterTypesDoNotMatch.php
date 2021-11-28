<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use LogicException;
use ReflectionParameter;

final class ParameterTypesDoNotMatch extends LogicException
{
    public function __construct(ReflectionParameter $parameter, Type $typeFromDocBlock, Type $typeFromReflection)
    {
        $signature = Reflection::signature($parameter);

        parent::__construct(
            "Types for parameter `$signature` do not match: `$typeFromDocBlock` (docblock) does not accept `$typeFromReflection` (native).",
            1617220595
        );
    }
}
