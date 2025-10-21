<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Definition\ParameterDefinition;

use function array_map;
use function implode;
use function iterator_to_array;
use function var_export;

/** @internal */
final class MethodDefinitionCompiler
{
    private TypeCompiler $typeCompiler;

    private AttributesCompiler $attributesCompiler;

    private ParameterDefinitionCompiler $parameterCompiler;

    public function __construct(TypeCompiler $typeCompiler, AttributesCompiler $attributesCompiler)
    {
        $this->typeCompiler = $typeCompiler;
        $this->attributesCompiler = $attributesCompiler;
        $this->parameterCompiler = new ParameterDefinitionCompiler($typeCompiler, $attributesCompiler);
    }

    public function compile(MethodDefinition $method): string
    {
        $attributes = $this->attributesCompiler->compile($method->attributes);

        $parameters = array_map(
            fn (ParameterDefinition $parameter) => $this->parameterCompiler->compile($parameter),
            iterator_to_array($method->parameters)
        );

        $parameters = implode(', ', $parameters);
        $isStatic = var_export($method->isStatic, true);
        $isPublic = var_export($method->isPublic, true);
        $returnType = $this->typeCompiler->compile($method->returnType);

        return <<<PHP
            new \CuyZ\Valinor\Definition\MethodDefinition(
                '{$method->name}',
                '{$method->signature}',
                $attributes,
                new \CuyZ\Valinor\Definition\Parameters($parameters),
                $isStatic,
                $isPublic,
                $returnType
            )
            PHP;
    }
}
