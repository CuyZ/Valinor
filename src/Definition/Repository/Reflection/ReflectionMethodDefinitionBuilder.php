<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\FunctionReturnTypeResolver;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ReflectionTypeResolver;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\TemplateResolver;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use ReflectionMethod;
use ReflectionParameter;

use function array_map;

/** @internal */
final class ReflectionMethodDefinitionBuilder
{
    private AttributesRepository $attributesRepository;

    private ReflectionParameterDefinitionBuilder $parameterBuilder;

    private TemplateResolver $templateResolver;

    public function __construct(
        AttributesRepository $attributesRepository,
        private TypeParserFactory $typeParserFactory,
    ) {
        $this->attributesRepository = $attributesRepository;
        $this->parameterBuilder = new ReflectionParameterDefinitionBuilder($attributesRepository);
        $this->templateResolver = new TemplateResolver();
    }

    public function for(ReflectionMethod $reflection, ReflectionTypeResolver $typeResolver): MethodDefinition
    {
        $signature = $reflection->getDeclaringClass()->name . '::' . $reflection->name . '()';

        $typeParser = $this->typeParserFactory->buildAdvancedTypeParserForClass($reflection->getDeclaringClass()->name);

        $methodTemplates = $this->templateResolver->templatesFromDocBlock($reflection, $signature, $typeParser);

        $typeResolver = $typeResolver->withVacantTypes($methodTemplates);

        $parameters = array_map(
            fn (ReflectionParameter $parameter) => $this->parameterBuilder->for($parameter, $typeResolver),
            $reflection->getParameters()
        );

        $returnTypeResolver = new FunctionReturnTypeResolver($typeResolver);

        $returnType = $returnTypeResolver->resolveReturnTypeFor($reflection);
        $nativeReturnType = $returnTypeResolver->resolveNativeReturnTypeFor($reflection);

        if ($returnType instanceof UnresolvableType) {
            $returnType = $returnType->forMethodReturnType($signature);
        } elseif (! $returnType->matches($nativeReturnType)) {
            $returnType = UnresolvableType::forNonMatchingTypes($nativeReturnType, $returnType)->forMethodReturnType($signature);
        }

        return new MethodDefinition(
            $reflection->name,
            $signature,
            new Attributes(...$this->attributesRepository->for($reflection)),
            new Parameters(...$parameters),
            $reflection->isStatic(),
            $reflection->isPublic(),
            $returnType
        );
    }
}
