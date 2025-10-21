<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Annotations;
use ReflectionParameter;

use function array_search;

/** @internal */
final class ParameterTypeResolver
{
    public function __construct(private ReflectionTypeResolver $typeResolver) {}

    public function resolveTypeFor(ReflectionParameter $reflection): Type
    {
        $docBlockType = null;

        if ($reflection->isPromoted()) {
            // @phpstan-ignore-next-line / parameter is promoted so class exists for sure
            $property = $reflection->getDeclaringClass()->getProperty($reflection->name);

            $docBlockType = Annotations::forProperty($property);
        }

        if ($docBlockType === null) {
            $docBlockType = $this->extractTypeFromDocBlock($reflection);
        }

        $type = $this->typeResolver->resolveType($reflection->getType(), $docBlockType);

        if ($reflection->isVariadic() && ! $type instanceof UnresolvableType) {
            return new ArrayType(ArrayKeyType::default(), $type);
        }

        return $type;
    }

    public function resolveNativeTypeFor(ReflectionParameter $reflection): Type
    {
        $type = $this->typeResolver->resolveNativeType($reflection->getType());

        if ($reflection->isVariadic()) {
            return new ArrayType(ArrayKeyType::default(), $type);
        }

        return $type;
    }

    private function extractTypeFromDocBlock(ReflectionParameter $reflection): ?string
    {
        $annotations = Annotations::forParameters($reflection->getDeclaringFunction());

        foreach ($annotations as $annotation) {
            $tokens = $annotation->filtered();

            $dollarSignKey = array_search('$', $tokens, true);

            if ($dollarSignKey === false) {
                continue;
            }

            $parameterName = $tokens[$dollarSignKey + 1] ?? null;

            if ($parameterName === $reflection->name) {
                return $annotation->raw();
            }
        }

        return null;
    }
}
