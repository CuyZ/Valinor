<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\Attributes;

use function array_map;
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

            if ($attribute->arguments === []) {
                $arguments = '';
            } else {
                $arguments = implode(', ', array_map(
                    fn (mixed $argument) => $this->compileAttributeArguments($argument),
                    $attribute->arguments,
                ));
            }

            $attributesListCode[] = <<<PHP
            new \CuyZ\Valinor\Definition\AttributeDefinition(
                $class,
                [$arguments],
            )
            PHP;
        }

        return implode(', ', $attributesListCode);
    }

    private function compileAttributeArguments(mixed $value): string
    {
        if (is_object($value)) {
            return 'unserialize(' . var_export(serialize($value), true) . ')';
        }

        if (is_array($value)) {
            $parts = [];

            foreach ($value as $key => $subValue) {
                $parts[] = var_export($key, true) . ' => ' . $this->compileAttributeArguments($subValue);
            }

            return '[' . implode(', ', $parts) . ']';
        }

        return var_export($value, true);
    }
}
