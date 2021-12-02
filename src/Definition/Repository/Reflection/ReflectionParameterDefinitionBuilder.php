<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Definition\Exception\InvalidParameterDefaultValue;
use CuyZ\Valinor\Definition\Exception\ParameterTypesDoNotMatch;
use CuyZ\Valinor\Definition\ParameterDefinition;
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
use ReflectionParameter;

use function trim;

final class ReflectionParameterDefinitionBuilder
{
    private TypeParserFactory $typeParserFactory;

    private AttributesRepository $attributesFactory;

    public function __construct(TypeParserFactory $typeParserFactory, AttributesRepository $attributesRepository)
    {
        $this->typeParserFactory = $typeParserFactory;
        $this->attributesFactory = $attributesRepository;
    }

    public function for(ClassSignature $classSignature, ReflectionParameter $reflection): ParameterDefinition
    {
        $name = $reflection->name;
        $signature = Reflection::signature($reflection);
        $type = $this->resolveType($classSignature, $reflection);
        $isOptional = $reflection->isOptional();
        $defaultValue = $reflection->isDefaultValueAvailable() ? $reflection->getDefaultValue() : null;
        $attributes = $this->attributesFactory->for($reflection);

        if ($isOptional
            && ! $type instanceof UnresolvableType
            && ! $type->accepts($defaultValue)
        ) {
            throw new InvalidParameterDefaultValue($reflection, $type);
        }

        return new ParameterDefinition($name, $signature, $type, $isOptional, $defaultValue, $attributes);
    }

    private function resolveType(ClassSignature $signature, ReflectionParameter $reflection): Type
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
            throw new ParameterTypesDoNotMatch($reflection, $typeFromDocBlock, $nativeType);
        }

        return $typeFromDocBlock;
    }

    private function typeFromDocBlock(ClassSignature $signature, ReflectionParameter $reflection): ?Type
    {
        $type = Reflection::docBlockType($reflection);

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

    private function nativeType(ReflectionParameter $reflection): ?Type
    {
        $reflectionType = $reflection->getType();

        if (! $reflectionType) {
            return null;
        }

        $class = $reflection->getDeclaringClass();

        assert($class !== null);

        $type = Reflection::flattenType($reflectionType);
        $parser = $this->typeParserFactory->get(
            new ClassContextSpecification($class->name)
        );

        return $this->parseType($type, $reflection, $parser);
    }

    private function parseType(string $raw, ReflectionParameter $reflection, TypeParser $parser): Type
    {
        try {
            return $parser->parse($raw);
        } catch (InvalidType $exception) {
            $raw = trim($raw);
            $signature = Reflection::signature($reflection);

            return new UnresolvableType(
                "The type `$raw` for parameter `$signature` could not be resolved: {$exception->getMessage()}"
            );
        }
    }
}
