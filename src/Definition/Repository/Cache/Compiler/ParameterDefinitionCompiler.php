<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\ParameterDefinition;

use function is_object;
use function serialize;
use function var_export;

/** @internal */
final class ParameterDefinitionCompiler
{
    public function __construct(
        private TypeCompiler $typeCompiler,
        private AttributesCompiler $attributesCompiler
    ) {}

    public function compile(ParameterDefinition $parameter): string
    {
        $isOptional = var_export($parameter->isOptional, true);
        $isVariadic = var_export($parameter->isVariadic, true);
        $defaultValue = $this->defaultValue($parameter);
        $type = $this->typeCompiler->compile($parameter->type);
        $nativeType = $this->typeCompiler->compile($parameter->nativeType);
        $attributes = $this->attributesCompiler->compile($parameter->attributes);

        return <<<PHP
            new \CuyZ\Valinor\Definition\ParameterDefinition(
                '{$parameter->name}',
                '{$parameter->signature}',
                $type,
                $nativeType,
                $isOptional,
                $isVariadic,
                $defaultValue,
                $attributes
            )
            PHP;
    }

    private function defaultValue(ParameterDefinition $parameter): string
    {
        $defaultValue = $parameter->defaultValue;

        return is_object($defaultValue)
            ? 'unserialize(' . var_export(serialize($defaultValue), true) . ')'
            : var_export($defaultValue, true);
    }
}
