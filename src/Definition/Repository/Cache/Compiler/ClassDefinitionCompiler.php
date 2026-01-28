<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\ClassDefinition;

use function array_map;
use function implode;
use function iterator_to_array;
use function var_export;

/** @internal */
final class ClassDefinitionCompiler
{
    private TypeCompiler $typeCompiler;

    private AttributesCompiler $attributesCompiler;

    private MethodDefinitionCompiler $methodCompiler;

    private PropertyDefinitionCompiler $propertyCompiler;

    public function __construct()
    {
        $this->attributesCompiler = new AttributesCompiler($this);
        $this->typeCompiler = new TypeCompiler($this->attributesCompiler);

        $this->methodCompiler = new MethodDefinitionCompiler($this->typeCompiler, $this->attributesCompiler);
        $this->propertyCompiler = new PropertyDefinitionCompiler($this->typeCompiler, $this->attributesCompiler);
    }

    public function compile(ClassDefinition $value): string
    {
        $name = var_export($value->name, true);
        $type = $this->typeCompiler->compile($value->type);

        $properties = array_map(
            $this->propertyCompiler->compile(...),
            iterator_to_array($value->properties)
        );

        $properties = implode(', ', $properties);

        $methods = array_map(
            $this->methodCompiler->compile(...),
            iterator_to_array($value->methods)
        );

        $methods = implode(', ', $methods);
        $attributes = $this->attributesCompiler->compile($value->attributes);

        $isFinal = var_export($value->isFinal, true);
        $isAbstract = var_export($value->isAbstract, true);

        return <<<PHP
        new \CuyZ\Valinor\Definition\ClassDefinition(
            $name,
            $type,
            $attributes,
            new \CuyZ\Valinor\Definition\Properties($properties),
            new \CuyZ\Valinor\Definition\Methods($methods),
            $isFinal,
            $isAbstract,
        )
        PHP;
    }
}
