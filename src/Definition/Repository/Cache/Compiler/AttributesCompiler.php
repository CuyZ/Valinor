<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\AttributesContainer;
use CuyZ\Valinor\Definition\NativeAttributes;
use ReflectionAttribute;

use function array_map;
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

    /**
     * @param ReflectionAttribute<object> $reflectionAttribute
     */
    private function compileNativeAttribute(ReflectionAttribute $reflectionAttribute): string
    {
        $name = $reflectionAttribute->getName();
        $arguments = $reflectionAttribute->getArguments();

        /** @infection-ignore-all */
        if (count($arguments) > 0) {
            $arguments = serialize($arguments);
            $arguments = 'unserialize(' . var_export($arguments, true) . ')';

            return "new $name(...$arguments)";
        }

        return "new $name()";
    }

    private function compileNativeAttributes(NativeAttributes $attributes): string
    {
        $attributesListCode = array_map(
            fn (ReflectionAttribute $attribute) => $this->compileNativeAttribute($attribute),
            $attributes->reflectionAttributes()
        );

        return '...[' . implode(",\n", $attributesListCode) . ']';
    }
}
