<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Type\Parser\Lexer\Annotations;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use ReflectionParameter;

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

            $docBlockType = (new PropertyTypeResolver($this->typeResolver))->extractTypeFromDocBlock($property);
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
        $docBlock = $reflection->getDeclaringFunction()->getDocComment();

        if ($docBlock === false) {
            return null;
        }

        $annotations = (new Annotations($docBlock))->filteredByPriority(
            '@phpstan-param',
            '@psalm-param',
            '@param',
        );

        foreach ($annotations as $annotation) {
            $tokens = $annotation->filtered();

            $dollarSignKey = array_search('$', $tokens, true);

            if ($dollarSignKey === false) {
                continue;
            }

            $parameterName = $tokens[$dollarSignKey + 1] ?? null;

            if ($parameterName === $reflection->name) {
                return $annotation->splice($dollarSignKey);
            }
        }

        return null;
    }
}
