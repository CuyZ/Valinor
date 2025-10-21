<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\ParameterDefinition;

use function array_map;
use function implode;
use function iterator_to_array;
use function var_export;

/** @internal */
final class FunctionDefinitionCompiler
{
    private TypeCompiler $typeCompiler;

    private AttributesCompiler $attributesCompiler;

    private ParameterDefinitionCompiler $parameterCompiler;

    public function __construct()
    {
        $this->attributesCompiler = new AttributesCompiler(new ClassDefinitionCompiler());
        $this->typeCompiler = new TypeCompiler($this->attributesCompiler);

        $this->parameterCompiler = new ParameterDefinitionCompiler($this->typeCompiler, $this->attributesCompiler);
    }

    public function compile(FunctionDefinition $value): string
    {
        $parameters = array_map(
            fn (ParameterDefinition $parameter) => $this->parameterCompiler->compile($parameter),
            iterator_to_array($value->parameters)
        );

        $attributes = $this->attributesCompiler->compile($value->attributes);
        $fileName = var_export($value->fileName, true);
        $class = var_export($value->class, true);
        $isStatic = var_export($value->isStatic, true);
        $isClosure = var_export($value->isClosure, true);
        $parameters = implode(', ', $parameters);
        $returnType = $this->typeCompiler->compile($value->returnType);

        return <<<PHP
            new \CuyZ\Valinor\Definition\FunctionDefinition(
                '{$value->name}',
                '{$value->signature}',
                $attributes,
                $fileName,
                $class,
                $isStatic,
                $isClosure,
                new \CuyZ\Valinor\Definition\Parameters($parameters),
                $returnType
            )
            PHP;
    }
}
