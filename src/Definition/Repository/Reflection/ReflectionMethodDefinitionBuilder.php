<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Definition\Exception\MethodReturnTypesDoNotMatch;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassAliasSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassContextSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\TypeAliasAssignerSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\HandleClassGenericSpecification;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionMethod;
use ReflectionParameter;

use function array_map;
use function trim;

final class ReflectionMethodDefinitionBuilder
{
    private TypeParserFactory $typeParserFactory;

    private ReflectionParameterDefinitionBuilder $parameterBuilder;

    public function __construct(TypeParserFactory $typeParserFactory, AttributesRepository $attributesRepository)
    {
        $this->typeParserFactory = $typeParserFactory;
        $this->parameterBuilder = new ReflectionParameterDefinitionBuilder($typeParserFactory, $attributesRepository);
    }

    public function for(ClassSignature $signature, ReflectionMethod $reflection): MethodDefinition
    {
        $parameters = array_map(
            fn (ReflectionParameter $parameter) => $this->parameterBuilder->for($signature, $parameter),
            $reflection->getParameters()
        );

        $returnType = $this->resolveType($signature, $reflection);

        return new MethodDefinition(
            $reflection->name,
            Reflection::signature($reflection),
            new Parameters(...$parameters),
            $reflection->isStatic(),
            $reflection->isPublic(),
            $returnType
        );
    }

    private function resolveType(ClassSignature $signature, ReflectionMethod $reflection): Type
    {
        $nativeType = $this->nativeType($reflection);
        $typeFromDocBlock = $this->typeFromDocBlock($signature, $reflection);

        if (! $nativeType && ! $typeFromDocBlock) {
            return MixedType::get();
        }

        if (! $nativeType) {
            /** @var Type $typeFromDocBlock */
            return $typeFromDocBlock;
        }

        if (! $typeFromDocBlock) {
            return $nativeType;
        }

        if (! $typeFromDocBlock instanceof UnresolvableType
            && ! $nativeType instanceof UnresolvableType
            && ! $typeFromDocBlock->matches($nativeType)
        ) {
            throw new MethodReturnTypesDoNotMatch($reflection, $typeFromDocBlock, $nativeType);
        }

        return $typeFromDocBlock;
    }

    private function typeFromDocBlock(ClassSignature $signature, ReflectionMethod $reflection): ?Type
    {
        $type = Reflection::docBlockReturnType($reflection);

        if ($type === null) {
            return null;
        }

        $parser = $this->typeParserFactory->get(
            new ClassContextSpecification($signature->className()),
            new ClassAliasSpecification($signature->className()),
            new HandleClassGenericSpecification(),
            new TypeAliasAssignerSpecification($signature->generics()),
        );

        return $this->parseType($type, $reflection, $parser);
    }

    private function nativeType(ReflectionMethod $reflection): ?Type
    {
        $reflectionType = $reflection->getReturnType();

        if (! $reflectionType) {
            return null;
        }

        $type = Reflection::flattenType($reflectionType);
        $parser = $this->typeParserFactory->get(
            new ClassContextSpecification($reflection->getDeclaringClass()->name)
        );

        return $this->parseType($type, $reflection, $parser);
    }

    private function parseType(string $raw, ReflectionMethod $reflection, TypeParser $parser): Type
    {
        try {
            return $parser->parse($raw);
        } catch (InvalidType $exception) {
            $raw = trim($raw);
            $signature = Reflection::signature($reflection);

            return new UnresolvableType(
                "The type `$raw` for return type of method `$signature` could not be resolved: {$exception->getMessage()}"
            );
        }
    }
}
