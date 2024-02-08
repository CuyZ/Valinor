<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\Attributes;

use function count;
use function implode;
use function is_array;
use function is_object;
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
            $arguments = $this->compileAttributeArguments($attribute->arguments);

            $attributesListCode[] = <<<PHP
            new \CuyZ\Valinor\Definition\AttributeDefinition(
                $class,
                [$arguments],
            )
            PHP;
        }

        return implode(', ', $attributesListCode);
    }

    /**
     * @param array<mixed> $arguments
     */
    private function compileAttributeArguments(array $arguments): string
    {
        if (count($arguments) === 0) {
            return '';
        }

        $argumentsCode = [];

        foreach ($arguments as $argument) {
            if (is_object($argument)) {
                $argumentsCode[] = 'unserialize(' . var_export(serialize($argument), true) . ')';
            } elseif (is_array($argument)) {
                $argumentsCode[] = '[' . $this->compileAttributeArguments($argument) . ']';
            } else {
                $argumentsCode[] = var_export($argument, true);
            }
        }

        return implode(', ', $argumentsCode);
    }
}
