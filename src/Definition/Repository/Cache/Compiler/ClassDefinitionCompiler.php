<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Cache\Compiled\CacheCompiler;
use CuyZ\Valinor\Cache\Compiled\CacheValidationCompiler;
use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Definition\PropertyDefinition;
use CuyZ\Valinor\Utility\Reflection\Reflection;

use function array_map;
use function assert;
use function filemtime;
use function implode;
use function iterator_to_array;

/** @internal */
final class ClassDefinitionCompiler implements CacheCompiler, CacheValidationCompiler
{
    private TypeCompiler $typeCompiler;

    private AttributesCompiler $attributesCompiler;

    private MethodDefinitionCompiler $methodCompiler;

    private PropertyDefinitionCompiler $propertyCompiler;

    private bool $validateCacheSource;

    public function __construct(bool $validateCacheSource)
    {
        $this->typeCompiler = new TypeCompiler();
        $this->attributesCompiler = new AttributesCompiler();

        $this->methodCompiler = new MethodDefinitionCompiler($this->typeCompiler, $this->attributesCompiler);
        $this->propertyCompiler = new PropertyDefinitionCompiler($this->typeCompiler, $this->attributesCompiler);
        $this->validateCacheSource = $validateCacheSource;
    }

    public function compile($value): string
    {
        assert($value instanceof ClassDefinition);

        $type = $this->typeCompiler->compile($value->type());

        $properties = array_map(
            fn (PropertyDefinition $property) => $this->propertyCompiler->compile($property),
            iterator_to_array($value->properties())
        );

        $properties = implode(', ', $properties);

        $methods = array_map(
            fn (MethodDefinition $method) => $this->methodCompiler->compile($method),
            iterator_to_array($value->methods())
        );

        $methods = implode(', ', $methods);
        $attributes = $this->attributesCompiler->compile($value->attributes());

        return <<<PHP
        new \CuyZ\Valinor\Definition\ClassDefinition(
            $type,
            $attributes,
            new \CuyZ\Valinor\Definition\Properties($properties),
            new \CuyZ\Valinor\Definition\Methods($methods)
        )
        PHP;
    }

    public function compileValidation($value): string
    {
        assert($value instanceof ClassDefinition);

        if ($this->validateCacheSource === false) {
            return 'true';
        }

        $filename = (Reflection::class($value->name()))->getFileName();

        // If the file does not exist it means it's a native class so the
        // definition is always valid (for a given PHP version).
        if (false === $filename) {
            return 'true';
        }

        $time = filemtime($filename);

        return "\\filemtime('$filename') === $time";
    }
}
