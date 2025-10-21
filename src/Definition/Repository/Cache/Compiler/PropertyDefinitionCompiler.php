<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\PropertyDefinition;

use function var_export;

/** @internal */
final class PropertyDefinitionCompiler
{
    public function __construct(
        private TypeCompiler $typeCompiler,
        private AttributesCompiler $attributesCompiler
    ) {}

    public function compile(PropertyDefinition $property): string
    {
        $type = $this->typeCompiler->compile($property->type);
        $nativeType = $this->typeCompiler->compile($property->nativeType);
        $hasDefaultValue = var_export($property->hasDefaultValue, true);
        $defaultValue = var_export($property->defaultValue, true);
        $isPublic = var_export($property->isPublic, true);
        $attributes = $this->attributesCompiler->compile($property->attributes);

        return <<<PHP
            new \CuyZ\Valinor\Definition\PropertyDefinition(
                '{$property->name}',
                '{$property->signature}',
                $type,
                $nativeType,
                $hasDefaultValue,
                $defaultValue,
                $isPublic,
                $attributes
            )
            PHP;
    }
}
