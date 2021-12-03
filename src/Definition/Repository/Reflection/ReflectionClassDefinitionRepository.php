<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Definition\Exception\ClassTypeAliasesDuplication;
use CuyZ\Valinor\Definition\Exception\InvalidTypeAliasImportClass;
use CuyZ\Valinor\Definition\Exception\InvalidTypeAliasImportClassType;
use CuyZ\Valinor\Definition\Exception\UnknownTypeAliasImport;
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
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionMethod;
use ReflectionProperty;

use function array_filter;
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
        $generics = $signature->generics();
        $localAliases = $this->localTypeAliases($signature);
        $importedAliases = $this->importedTypeAliases($signature);

        $duplicates = [];
        $keys = [...array_keys($generics), ...array_keys($localAliases), ...array_keys($importedAliases)];

        foreach ($keys as $key) {
            $sameKeys = array_filter($keys, fn ($value) => $value === $key);

            if (count($sameKeys) > 1) {
                $duplicates[$key] = true;
            }
        }

        if (count($duplicates) > 0) {
            throw new ClassTypeAliasesDuplication($signature->className(), ...array_keys($duplicates));
        }

        $advancedParser = $this->typeParserFactory->get(
            new ClassContextSpecification($signature->className()),
            new ClassAliasSpecification($signature->className()),
            new HandleClassGenericSpecification(),
            new TypeAliasAssignerSpecification($generics + $localAliases + $importedAliases)
        );

        $nativeParser = $this->typeParserFactory->get(
            new ClassContextSpecification($signature->className())
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

        $typeParser = $this->typeParser($signature);

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

    /**
     * @return array<string, Type>
     */
    private function importedTypeAliases(ClassSignature $signature): array
    {
        $reflection = Reflection::class($signature->className());
        $importedTypesRaw = Reflection::importedTypeAliases($reflection);

        $typeParser = $this->typeParser($signature);

        $importedTypes = [];

        foreach ($importedTypesRaw as $class => $types) {
            try {
                $classType = $typeParser->parse($class);
            } catch (InvalidType $exception) {
                throw new InvalidTypeAliasImportClass($signature, $class);
            }

            if (! $classType instanceof ClassType) {
                throw new InvalidTypeAliasImportClassType($signature, $classType);
            }

            $localTypes = $this->localTypeAliases($classType->signature());

            foreach ($types as $type) {
                if (! isset($localTypes[$type])) {
                    throw new UnknownTypeAliasImport($signature, $classType->signature()->className(), $type);
                }

                $importedTypes[$type] = $localTypes[$type];
            }
        }

        return $importedTypes;
    }

    private function typeParser(ClassSignature $signature): TypeParser
    {
        return $this->typeParserFactory->get(
            new ClassContextSpecification($signature->className()),
            new ClassAliasSpecification($signature->className()),
            new HandleClassGenericSpecification(),
            new TypeAliasAssignerSpecification($signature->generics()),
        );
    }
}
