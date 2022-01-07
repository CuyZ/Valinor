<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\CombinedAttributes;
use CuyZ\Valinor\Definition\DoctrineAnnotations;
use CuyZ\Valinor\Definition\EmptyAttributes;
use CuyZ\Valinor\Definition\NativeAttributes;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\Exception\IncompatibleAttributes;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

use function array_map;
use function count;
use function implode;
use function var_export;

final class AttributesCompiler
{
    public function compile(Attributes $attributes): string
    {
        if (count($attributes) === 0) {
            return EmptyAttributes::class . '::get()';
        }

        if ($attributes instanceof CombinedAttributes) {
            $attributesListCode = $this->compileCombinedAttributes($attributes);
        } elseif ($attributes instanceof DoctrineAnnotations) {
            $attributesListCode = $this->compileDoctrineAnnotations($attributes);
        } elseif ($attributes instanceof NativeAttributes) {
            $attributesListCode = $this->compileNativeAttributes($attributes);
        } else {
            throw new IncompatibleAttributes($attributes);
        }

        return <<<PHP
            new \CuyZ\Valinor\Definition\AttributesContainer($attributesListCode)
            PHP;
    }

    private function compileDoctrineAnnotations(DoctrineAnnotations $annotations): string
    {
        if (count($annotations) === 0) {
            return '...[]';
        }

        $reflection = $annotations->reflection();

        if ($reflection instanceof ReflectionClass) {
            return <<<PHP
                ...\CuyZ\Valinor\Utility\Singleton::annotationReader()->getClassAnnotations(
                    new \ReflectionClass('$reflection->name')
                )
            PHP;
        }

        if ($reflection instanceof ReflectionProperty) {
            return <<<PHP
                ...\CuyZ\Valinor\Utility\Singleton::annotationReader()->getPropertyAnnotations(
                    new \ReflectionProperty(
                        '{$reflection->getDeclaringClass()->name}',
                        '$reflection->name'
                    )
                )
            PHP;
        }

        /** @var ReflectionMethod $reflection */
        return <<<PHP
            ...\CuyZ\Valinor\Utility\Singleton::annotationReader()->getMethodAnnotations(
                new \ReflectionMethod(
                    '{$reflection->getDeclaringClass()->name}',
                    '$reflection->name'
                )
            )
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

    private function compileCombinedAttributes(CombinedAttributes $attributes): string
    {
        $annotationsCode = $this->compileDoctrineAnnotations($attributes->doctrineAnnotations());

        $nativeAttributes = $attributes->nativeAttributes();

        if (null === $nativeAttributes) {
            return $annotationsCode;
        }

        return $annotationsCode . ', ' . $this->compileNativeAttributes($nativeAttributes);
    }
}
