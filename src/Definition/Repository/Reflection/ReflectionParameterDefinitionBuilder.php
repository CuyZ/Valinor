<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\Exception\InvalidParameterDefaultValue;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionParameter;

final class ReflectionParameterDefinitionBuilder
{
    private AttributesRepository $attributesFactory;

    public function __construct(AttributesRepository $attributesRepository)
    {
        $this->attributesFactory = $attributesRepository;
    }

    public function for(ReflectionParameter $reflection, ReflectionTypeResolver $typeResolver): ParameterDefinition
    {
        $name = $reflection->name;
        $signature = Reflection::signature($reflection);
        $type = $typeResolver->resolveType($reflection);
        $isOptional = $reflection->isOptional();
        $defaultValue = $reflection->isDefaultValueAvailable() ? $reflection->getDefaultValue() : null;
        $attributes = $this->attributesFactory->for($reflection);

        if ($isOptional
            && ! $type instanceof UnresolvableType
            && ! $type->accepts($defaultValue)
        ) {
            throw new InvalidParameterDefaultValue($reflection, $type);
        }

        return new ParameterDefinition($name, $signature, $type, $isOptional, $defaultValue, $attributes);
    }
}
