<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\AliasSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassContextSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\GenericCheckerSpecification;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionAttribute;
use ReflectionFunction;
use ReflectionParameter;

use function array_map;
use function str_ends_with;

/** @internal */
final class ReflectionFunctionDefinitionRepository implements FunctionDefinitionRepository
{
    private TypeParserFactory $typeParserFactory;

    private AttributesRepository $attributesRepository;

    private ReflectionParameterDefinitionBuilder $parameterBuilder;

    public function __construct(TypeParserFactory $typeParserFactory, AttributesRepository $attributesRepository)
    {
        $this->typeParserFactory = $typeParserFactory;
        $this->attributesRepository = $attributesRepository;
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

        $name = $reflection->getName();
        $class = $reflection->getClosureScopeClass();
        $returnType = $typeResolver->resolveType($reflection);
        $isClosure = $name === '{closure}' || str_ends_with($name, '\\{closure}');

        return new FunctionDefinition(
            $name,
            Reflection::signature($reflection),
            new Attributes(...$this->attributes($reflection)),
            $reflection->getFileName() ?: null,
            $class?->name,
            $reflection->getClosureThis() === null,
            $isClosure,
            new Parameters(...$parameters),
            $returnType
        );
    }

    private function typeResolver(ReflectionFunction $reflection): ReflectionTypeResolver
    {
        $class = $reflection->getClosureScopeClass();

        $nativeSpecifications = [];
        $advancedSpecification = [
            new AliasSpecification($reflection),
            new GenericCheckerSpecification(),
        ];

        if ($class !== null) {
            $nativeSpecifications[] = new ClassContextSpecification($class->name);
            $advancedSpecification[] = new ClassContextSpecification($class->name);
        }

        $nativeParser = $this->typeParserFactory->get(...$nativeSpecifications);
        $advancedParser = $this->typeParserFactory->get(...$advancedSpecification);

        return new ReflectionTypeResolver($nativeParser, $advancedParser);
    }

    /**
     * @return list<AttributeDefinition>
     */
    private function attributes(ReflectionFunction $reflection): array
    {
        return array_map(
            fn (ReflectionAttribute $attribute) => $this->attributesRepository->for($attribute),
            Reflection::attributes($reflection)
        );
    }
}
