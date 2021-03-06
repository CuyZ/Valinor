<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Cache\Compiled\CacheCompiler;
use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\ParameterDefinition;

use function var_export;

/** @internal */
final class FunctionDefinitionCompiler implements CacheCompiler
{
    private TypeCompiler $typeCompiler;

    private ParameterDefinitionCompiler $parameterCompiler;

    public function __construct()
    {
        $this->typeCompiler = new TypeCompiler();
        $this->parameterCompiler = new ParameterDefinitionCompiler($this->typeCompiler, new AttributesCompiler());
    }

    public function compile($value): string
    {
        assert($value instanceof FunctionDefinition);

        $parameters = array_map(
            fn (ParameterDefinition $parameter) => $this->parameterCompiler->compile($parameter),
            iterator_to_array($value->parameters())
        );

        $fileName = var_export($value->fileName(), true);
        $class = var_export($value->class(), true);
        $parameters = implode(', ', $parameters);
        $returnType = $this->typeCompiler->compile($value->returnType());

        return <<<PHP
            new \CuyZ\Valinor\Definition\FunctionDefinition(
                '{$value->name()}',
                '{$value->signature()}',
                $fileName,
                $class,
                new \CuyZ\Valinor\Definition\Parameters($parameters),
                $returnType
            )
            PHP;
    }
}
