<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\AliasSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassContextSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\HandleClassGenericSpecification;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionFunction;
use ReflectionParameter;

/** @internal */
final class ReflectionFunctionDefinitionRepository implements FunctionDefinitionRepository
{
    private TypeParserFactory $typeParserFactory;

    private ReflectionParameterDefinitionBuilder $parameterBuilder;

    public function __construct(TypeParserFactory $typeParserFactory, AttributesRepository $attributesRepository)
    {
        $this->typeParserFactory = $typeParserFactory;
        $this->parameterBuilder = new ReflectionParameterDefinitionBuilder($attributesRepository);
    }

    public function for(callable $function): FunctionDefinition
    {
        $reflection = Reflection::function($function);

        $typeResolver = $this->typeResolver($reflection);

        $parameters = array_map(
            fn (ReflectionParameter $parameter) => $this->parameterBuilder->for($parameter, $typeResolver),
            $reflection->getParameters()
        );

        $returnType = $typeResolver->resolveType($reflection);

        return new FunctionDefinition(
            $reflection->getName(),
            Reflection::signature($reflection),
            $reflection->getFileName() ?: null,
            // @PHP 8.0 nullsafe operator
            $reflection->getClosureScopeClass() ? $reflection->getClosureScopeClass()->name : null,
            new Parameters(...$parameters),
            $returnType
        );
    }

    private function typeResolver(ReflectionFunction $reflection): ReflectionTypeResolver
    {
        $class = $reflection->getClosureScopeClass();

        $nativeSpecifications = [];
        $advancedSpecification = [new AliasSpecification($reflection)];

        if ($class !== null) {
            $nativeSpecifications[] = new ClassContextSpecification($class->name);
            $advancedSpecification[] = new ClassContextSpecification($class->name);
            $advancedSpecification[] = new HandleClassGenericSpecification();
        }

        $nativeParser = $this->typeParserFactory->get(...$nativeSpecifications);
        $advancedParser = $this->typeParserFactory->get(...$advancedSpecification);

        return new ReflectionTypeResolver($nativeParser, $advancedParser);
    }
}
