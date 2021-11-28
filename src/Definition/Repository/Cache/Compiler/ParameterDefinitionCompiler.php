<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\ParameterDefinition;

final class ParameterDefinitionCompiler
{
    private TypeCompiler $typeCompiler;

    private AttributesCompiler $attributesCompiler;

    public function __construct(TypeCompiler $typeCompiler, AttributesCompiler $attributesCompiler)
    {
        $this->typeCompiler = $typeCompiler;
        $this->attributesCompiler = $attributesCompiler;
    }

    public function compile(ParameterDefinition $parameter): string
    {
        $isOptional = var_export($parameter->isOptional(), true);
        $defaultValue = var_export($parameter->defaultValue(), true);
        $type = $this->typeCompiler->compile($parameter->type());
        $attributes = $this->attributesCompiler->compile($parameter->attributes());

        return <<<PHP
            new \CuyZ\Valinor\Definition\ParameterDefinition(
                '{$parameter->name()}',
                '{$parameter->signature()}',
                $type,
                $isOptional,
                $defaultValue,
                $attributes
            )
            PHP;
    }
}
