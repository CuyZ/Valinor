<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\Attributes;

use function array_map;
use function count;
use function implode;
use function var_export;

/** @internal */
final class AttributesCompiler
{
    public function __construct(private ClassDefinitionCompiler $classDefinitionCompiler) {}

    public function compile(Attributes $attributes): string
    {
        if (count($attributes) === 0) {
            return Attributes::class . '::empty()';
        }

        $attributesListCode = $this->compileAttributes($attributes);

        return <<<PHP
            new \CuyZ\Valinor\Definition\Attributes($attributesListCode)
            PHP;
    }

    private function compileAttributes(Attributes $attributes): string
    {
        $attributesListCode = [];

        foreach ($attributes as $attribute) {
            $class = $this->classDefinitionCompiler->compile($attribute->class);
            $argumentsCode = $this->compileAttributeArguments($attribute->arguments);
            $reflectionParts = var_export($attribute->reflectionParts, true);
            $attributeIndex = var_export($attribute->attributeIndex, true);

            $attributesListCode[] = <<<PHP
            new \CuyZ\Valinor\Definition\AttributeDefinition(
                $class,
                $argumentsCode,
                $reflectionParts,
                $attributeIndex,
            )
            PHP;
        }

        return implode(', ', $attributesListCode);
    }

    /**
     * @param null|list<array<scalar>|scalar> $arguments
     */
    private function compileAttributeArguments(?array $arguments): string
    {
        if ($arguments === null) {
            return 'null';
        }

        $code = array_map(static fn ($value) => var_export($value, true), $arguments);

        return '[' . implode(', ', $code) . ']';
    }
}
