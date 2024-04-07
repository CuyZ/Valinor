<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ParameterTypeResolver;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ReflectionTypeResolver;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionAttribute;
use ReflectionParameter;

use function array_map;

/** @internal */
final class ReflectionParameterDefinitionBuilder
{
    public function __construct(private AttributesRepository $attributesRepository) {}

    public function for(ReflectionParameter $reflection, ReflectionTypeResolver $typeResolver): ParameterDefinition
    {
        $parameterTypeResolver = new ParameterTypeResolver($typeResolver);

        /** @var non-empty-string $name */
        $name = $reflection->name;
        $signature = $this->signature($reflection);
        $type = $parameterTypeResolver->resolveTypeFor($reflection);
        $nativeType = $parameterTypeResolver->resolveNativeTypeFor($reflection);
        $isOptional = $reflection->isOptional();
        $isVariadic = $reflection->isVariadic();

        if ($reflection->isDefaultValueAvailable()) {
            $defaultValue = $reflection->getDefaultValue();
        } elseif ($reflection->isVariadic()) {
            $defaultValue = [];
        } else {
            $defaultValue = null;
        }

        if ($type instanceof UnresolvableType) {
            $type = $type->forParameter($signature);
        } elseif (! $type->matches($nativeType)) {
            $type = UnresolvableType::forNonMatchingParameterTypes($signature, $nativeType, $type);
        } elseif ($isOptional && ! $type->accepts($defaultValue)) {
            $type = UnresolvableType::forInvalidParameterDefaultValue($signature, $type, $defaultValue);
        }

        return new ParameterDefinition(
            $name,
            $signature,
            $type,
            $nativeType,
            $isOptional,
            $isVariadic,
            $defaultValue,
            new Attributes(...$this->attributes($reflection)),
        );
    }

    /**
     * @return list<AttributeDefinition>
     */
    private function attributes(ReflectionParameter $reflection): array
    {
        return array_map(
            fn (ReflectionAttribute $attribute) => $this->attributesRepository->for($attribute),
            Reflection::attributes($reflection)
        );
    }

    /**
     * @return non-empty-string
     */
    private function signature(ReflectionParameter $reflection): string
    {
        $signature = $reflection->getDeclaringFunction()->name . "(\$$reflection->name)";
        $class = $reflection->getDeclaringClass();

        if ($class) {
            $signature = $class->name . '::' . $signature;
        }

        return $signature;
    }
}
