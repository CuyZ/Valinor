<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Definition\Exception\InvalidPropertyDefaultValue;
use CuyZ\Valinor\Definition\Exception\PropertyTypesDoNotMatch;
use CuyZ\Valinor\Definition\PropertyDefinition;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassAliasSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassContextSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\HandleClassGenericSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\TypeAliasAssignerSpecification;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionProperty;

use function trim;

final class ReflectionPropertyDefinitionBuilder
{
    private TypeParserFactory $typeParserFactory;

    private AttributesRepository $attributesRepository;

    public function __construct(TypeParserFactory $typeParserFactory, AttributesRepository $attributesRepository)
    {
        $this->typeParserFactory = $typeParserFactory;
        $this->attributesRepository = $attributesRepository;
    }

    public function for(ClassSignature $classSignature, ReflectionProperty $reflection): PropertyDefinition
    {
        $name = $reflection->name;
        $signature = Reflection::signature($reflection);
        $type = $this->resolveType($classSignature, $reflection);
        $hasDefaultValue = $this->hasDefaultValue($reflection);
        $defaultValue = $this->defaultValue($reflection);
        $isPublic = $reflection->isPublic();
        $attributes = $this->attributesRepository->for($reflection);

        if ($hasDefaultValue
            && ! $type instanceof UnresolvableType
            && ! $type->accepts($defaultValue)
        ) {
            throw new InvalidPropertyDefaultValue($reflection, $type);
        }

        return new PropertyDefinition(
            $name,
            $signature,
            $type,
            $hasDefaultValue,
            $defaultValue,
            $isPublic,
            $attributes
        );
    }

    private function resolveType(ClassSignature $signature, ReflectionProperty $reflection): Type
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
            throw new PropertyTypesDoNotMatch($reflection, $typeFromDocBlock, $nativeType);
        }

        return $typeFromDocBlock;
    }

    private function typeFromDocBlock(ClassSignature $signature, ReflectionProperty $reflection): ?Type
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

    private function nativeType(ReflectionProperty $reflection): ?Type
    {
        $reflectionType = $reflection->getType();

        if (! $reflectionType) {
            return null;
        }

        $type = Reflection::flattenType($reflectionType);
        $parser = $this->typeParserFactory->get(
            new ClassContextSpecification($reflection->getDeclaringClass()->name)
        );

        return $this->parseType($type, $reflection, $parser);
    }

    private function parseType(string $raw, ReflectionProperty $reflection, TypeParser $parser): Type
    {
        try {
            return $parser->parse($raw);
        } catch (InvalidType $exception) {
            $raw = trim($raw);
            $signature = Reflection::signature($reflection);

            return new UnresolvableType(
                "The type `$raw` for property `$signature` could not be resolved: {$exception->getMessage()}"
            );
        }
    }

    private function hasDefaultValue(ReflectionProperty $reflection): bool
    {
        // @PHP8.0 `$reflection->hasDefaultValue()`
        $defaultProperties = $reflection->getDeclaringClass()->getDefaultProperties();

        return isset($defaultProperties[$reflection->name]);
    }

    /**
     * @return mixed
     */
    private function defaultValue(ReflectionProperty $reflection)
    {
        // @PHP8.0 `$reflection->getDefaultValue()`
        $defaultProperties = $reflection->getDeclaringClass()->getDefaultProperties();

        return $defaultProperties[$reflection->name] ?? null;
    }
}
