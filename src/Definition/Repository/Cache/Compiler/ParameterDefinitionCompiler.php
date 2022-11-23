<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\ParameterDefinition;

use function is_scalar;

/** @internal */
final class ParameterDefinitionCompiler
{
    public function __construct(
        private TypeCompiler $typeCompiler,
        private AttributesCompiler $attributesCompiler
    ) {
    }

    public function compile(ParameterDefinition $parameter): string
    {
        $isOptional = var_export($parameter->isOptional(), true);
        $isVariadic = var_export($parameter->isVariadic(), true);
        $defaultValue = $this->defaultValue($parameter);
        $type = $this->typeCompiler->compile($parameter->type());
        $attributes = $this->attributesCompiler->compile($parameter->attributes());

        return <<<PHP
            new \CuyZ\Valinor\Definition\ParameterDefinition(
                '{$parameter->name()}',
                '{$parameter->signature()}',
                $type,
                $isOptional,
                $isVariadic,
                $defaultValue,
                $attributes
            )
            PHP;
    }

    private function defaultValue(ParameterDefinition $parameter): string
    {
        $defaultValue = $parameter->defaultValue();

        return is_scalar($defaultValue)
            ? var_export($parameter->defaultValue(), true)
            : 'unserialize(' . var_export(serialize($defaultValue), true) . ')';
    }
}
