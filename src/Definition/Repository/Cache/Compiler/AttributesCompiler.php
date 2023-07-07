<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\AttributesContainer;
use CuyZ\Valinor\Definition\NativeAttributes;

use function count;
use function implode;
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
        $attributes = $attributes->definition();

        if (count($attributes) === 0) {
            return '[]';
        }

        $attributesListCode = [];

        foreach ($attributes as $className => $arguments) {
            if (count($arguments) === 0) {
                $argumentsCode = '';
            } else {
                $argumentsCode = '...unserialize(' . var_export(serialize($arguments), true) . ')';
            }

            $attributesListCode[] = "new $className($argumentsCode)";
        }

        return '...[' . implode(",\n", $attributesListCode) . ']';
    }
}
