<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\AttributesContainer;
use CuyZ\Valinor\Definition\NativeAttributes;

use function count;
use function implode;
use function is_array;
use function is_object;
use function var_export;

/** @internal */
final class AttributesCompiler
{
    public function compile(Attributes $attributes): string
    {
        if (count($attributes) === 0) {
            return AttributesContainer::class . '::empty()';
        }

        assert($attributes instanceof NativeAttributes);

        $attributesListCode = $this->compileNativeAttributes($attributes);

        return <<<PHP
            new \CuyZ\Valinor\Definition\AttributesContainer($attributesListCode)
            PHP;
    }

    private function compileNativeAttributes(NativeAttributes $attributes): string
    {
        $attributesListCode = [];

        foreach ($attributes->definition() as $className => $arguments) {
            $argumentsCode = $this->compileAttributeArguments($arguments);

            $attributesListCode[] = "['class' => '$className', 'callback' => fn () => new $className($argumentsCode)]";
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
