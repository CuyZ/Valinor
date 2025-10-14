<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\PropertyDefinition;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\PropertyTypeResolver;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ReflectionTypeResolver;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use ReflectionProperty;

/** @internal */
final class ReflectionPropertyDefinitionBuilder
{
    public function __construct(private AttributesRepository $attributesRepository) {}

    public function for(ReflectionProperty $reflection, ReflectionTypeResolver $typeResolver): PropertyDefinition
    {
        $propertyTypeResolver = new PropertyTypeResolver($typeResolver);

        /** @var non-empty-string $name */
        $name = $reflection->name;
        $signature = $reflection->getDeclaringClass()->name . '::$' . $reflection->name;
        $type = $propertyTypeResolver->resolveTypeFor($reflection);
        $nativeType = $propertyTypeResolver->resolveNativeTypeFor($reflection);
        $hasDefaultValue = $this->hasDefaultValue($reflection, $type);
        $defaultValue = $hasDefaultValue ? $reflection->getDefaultValue() : null;
        $isPublic = $reflection->isPublic();

        if ($type instanceof UnresolvableType) {
            $type = $type->forProperty($signature);
        } elseif (! $type->matches($nativeType)) {
            $type = UnresolvableType::forNonMatchingTypes($nativeType, $type)->forProperty($signature);
        } elseif ($hasDefaultValue && ! $type->accepts($defaultValue)) {
            $type = UnresolvableType::forInvalidDefaultValue($type, $defaultValue)->forProperty($signature);
        }

        return new PropertyDefinition(
            $name,
            $signature,
            $type,
            $nativeType,
            $hasDefaultValue,
            $defaultValue,
            $isPublic,
            new Attributes(...$this->attributesRepository->for($reflection)),
        );
    }

    private function hasDefaultValue(ReflectionProperty $reflection, Type $type): bool
    {
        if ($reflection->hasType()) {
            return $reflection->hasDefaultValue();
        }

        return $reflection->getDeclaringClass()->getDefaultProperties()[$reflection->name] !== null
            || NullType::get()->matches($type);
    }
}
