<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Reflection\Annotations;
use ReflectionProperty;

/** @internal */
final class PropertyTypeResolver
{
    public function __construct(private ReflectionTypeResolver $typeResolver) {}

    public function resolveTypeFor(ReflectionProperty $reflection): Type
    {
        $docBlockType = Annotations::forProperty($reflection);

        return $this->typeResolver->resolveType($reflection->getType(), $docBlockType);
    }

    public function resolveNativeTypeFor(ReflectionProperty $reflection): Type
    {
        return $this->typeResolver->resolveNativeType($reflection->getType());
    }
}
