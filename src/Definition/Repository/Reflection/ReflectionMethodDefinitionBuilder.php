<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\FunctionReturnTypeResolver;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ReflectionTypeResolver;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use ReflectionMethod;
use ReflectionParameter;

use function array_map;

/** @internal */
final class ReflectionMethodDefinitionBuilder
{
    private AttributesRepository $attributesRepository;

    private ReflectionParameterDefinitionBuilder $parameterBuilder;

    public function __construct(AttributesRepository $attributesRepository)
    {
        $this->attributesRepository = $attributesRepository;
        $this->parameterBuilder = new ReflectionParameterDefinitionBuilder($attributesRepository);
    }

    public function for(ReflectionMethod $reflection, ReflectionTypeResolver $typeResolver): MethodDefinition
    {
        $name = $reflection->name;
        $signature = $reflection->getDeclaringClass()->name . '::' . $reflection->name . '()';

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
            $name,
            $signature,
            new Attributes(...$this->attributesRepository->for($reflection)),
            new Parameters(...$parameters),
            $reflection->isStatic(),
            $reflection->isPublic(),
            $returnType
        );
    }
}
