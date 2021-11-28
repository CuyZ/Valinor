<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use LogicException;
use ReflectionProperty;

final class PropertyTypesDoNotMatch extends LogicException
{
    public function __construct(ReflectionProperty $property, Type $typeFromDocBlock, Type $typeFromReflection)
    {
        $signature = Reflection::signature($property);

        parent::__construct(
            "Types for property `$signature` do not match: `$typeFromDocBlock` (docblock) does not accept `$typeFromReflection` (native).",
            1617218939
        );
    }
}
