<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Definition\Exception\ClassTypeAliasesDuplication;
use CuyZ\Valinor\Definition\Methods;
use CuyZ\Valinor\Definition\Properties;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassAliasSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassContextSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\HandleClassGenericSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\TypeAliasAssignerSpecification;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionMethod;
use ReflectionProperty;

use function array_intersect_key;
use function array_keys;
use function array_map;

final class ReflectionClassDefinitionRepository implements ClassDefinitionRepository
{
    private AttributesRepository $attributesFactory;

    private TypeParserFactory $typeParserFactory;

    private ReflectionPropertyDefinitionBuilder $propertyBuilder;

    private ReflectionMethodDefinitionBuilder $methodBuilder;

    public function __construct(TypeParserFactory $typeParserFactory, AttributesRepository $attributesRepository)
    {
        $this->attributesFactory = $attributesRepository;
        $this->typeParserFactory = $typeParserFactory;

        $this->propertyBuilder = new ReflectionPropertyDefinitionBuilder($attributesRepository);
        $this->methodBuilder = new ReflectionMethodDefinitionBuilder($attributesRepository);
    }

    public function for(ClassSignature $signature): ClassDefinition
    {
        $reflection = Reflection::class($signature->className());
        $typeResolver = $this->typeResolver($signature);

        $properties = array_map(
            fn (ReflectionProperty $property) => $this->propertyBuilder->for($property, $typeResolver),
            $reflection->getProperties()
        );

        $methods = array_map(
            fn (ReflectionMethod $method) => $this->methodBuilder->for($method, $typeResolver),
            $reflection->getMethods()
        );

        return new ClassDefinition(
            $signature->className(),
            $signature->toString(),
            $this->attributesFactory->for($reflection),
            new Properties(...$properties),
            new Methods(...$methods),
        );
    }

    private function typeResolver(ClassSignature $signature): ReflectionTypeResolver
    {
        $nativeParser = $this->typeParserFactory->get(
            new ClassContextSpecification($signature->className())
        );

        $generics = $signature->generics();
        $localAliases = $this->localTypeAliases($signature);
        $duplicates = array_intersect_key($generics, $localAliases);

        if (count($duplicates) > 0) {
            throw new ClassTypeAliasesDuplication($signature->className(), ...array_keys($duplicates));
        }

        $aliases = $generics + $localAliases;

        $advancedParser = $this->typeParserFactory->get(
            new ClassContextSpecification($signature->className()),
            new ClassAliasSpecification($signature->className()),
            new HandleClassGenericSpecification(),
            new TypeAliasAssignerSpecification($aliases)
        );

        return new ReflectionTypeResolver($nativeParser, $advancedParser);
    }

    /**
     * @return array<string, Type>
     */
    private function localTypeAliases(ClassSignature $signature): array
    {
        $reflection = Reflection::class($signature->className());
        $rawTypes = Reflection::localTypeAliases($reflection);

        $typeParser = $this->typeParserFactory->get(
            new ClassContextSpecification($signature->className()),
            new ClassAliasSpecification($signature->className()),
            new HandleClassGenericSpecification(),
            new TypeAliasAssignerSpecification($signature->generics()),
        );

        $types = [];

        foreach ($rawTypes as $name => $raw) {
            try {
                $types[$name] = $typeParser->parse($raw);
            } catch (InvalidType $exception) {
                $raw = trim($raw);

                $types[$name] = new UnresolvableType(
                    "The type `$raw` for local alias `$name` of the class `{$signature->className()}` could not be resolved: {$exception->getMessage()}"
                );
            }
        }

        return $types;
    }
}
