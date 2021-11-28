<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use LogicException;
use ReflectionMethod;

final class MethodReturnTypesDoNotMatch extends LogicException
{
    public function __construct(ReflectionMethod $parameter, Type $typeFromDocBlock, Type $typeFromReflection)
    {
        $signature = Reflection::signature($parameter);

        parent::__construct(
            "Return types for method `$signature` do not match: `$typeFromDocBlock` (docblock) does not accept `$typeFromReflection` (native).",
            1634048916
        );
    }
}
