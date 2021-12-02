<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\Exception\InvalidPropertyDefaultValue;
use CuyZ\Valinor\Definition\PropertyDefinition;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionProperty;

final class ReflectionPropertyDefinitionBuilder
{
    private AttributesRepository $attributesRepository;

    public function __construct(AttributesRepository $attributesRepository)
    {
        $this->attributesRepository = $attributesRepository;
    }

    public function for(ReflectionProperty $reflection, ReflectionTypeResolver $typeResolver): PropertyDefinition
    {
        $name = $reflection->name;
        $signature = Reflection::signature($reflection);
        $type = $typeResolver->resolveType($reflection);
        $hasDefaultValue = $this->hasDefaultValue($reflection);
        $defaultValue = $this->defaultValue($reflection);
        $isPublic = $reflection->isPublic();
        $attributes = $this->attributesRepository->for($reflection);

        if ($hasDefaultValue
            && ! $type instanceof UnresolvableType
            && ! $type->accepts($defaultValue)
        ) {
            throw new InvalidPropertyDefaultValue($reflection, $type);
        }

        return new PropertyDefinition(
            $name,
            $signature,
            $type,
            $hasDefaultValue,
            $defaultValue,
            $isPublic,
            $attributes
        );
    }

    private function hasDefaultValue(ReflectionProperty $reflection): bool
    {
        // @PHP8.0 `$reflection->hasDefaultValue()`
        $defaultProperties = $reflection->getDeclaringClass()->getDefaultProperties();

        return isset($defaultProperties[$reflection->name]);
    }

    /**
     * @return mixed
     */
    private function defaultValue(ReflectionProperty $reflection)
    {
        // @PHP8.0 `$reflection->getDefaultValue()`
        $defaultProperties = $reflection->getDeclaringClass()->getDefaultProperties();

        return $defaultProperties[$reflection->name] ?? null;
    }
}
