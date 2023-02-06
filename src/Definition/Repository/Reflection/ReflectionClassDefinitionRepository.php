<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\Exception\ClassTypeAliasesDuplication;
use CuyZ\Valinor\Definition\Exception\InvalidTypeAliasImportClass;
use CuyZ\Valinor\Definition\Exception\InvalidTypeAliasImportClassType;
use CuyZ\Valinor\Definition\Exception\UnknownTypeAliasImport;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Definition\Methods;
use CuyZ\Valinor\Definition\Properties;
use CuyZ\Valinor\Definition\PropertyDefinition;
use CuyZ\Valinor\Definition\Repository\AttributesRepository;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Type\GenericType;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\AliasSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassContextSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\TypeAliasAssignerSpecification;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionMethod;
use ReflectionProperty;

use function array_filter;
use function array_keys;
use function array_map;

/** @internal */
final class ReflectionClassDefinitionRepository implements ClassDefinitionRepository
{
    private TypeParserFactory $typeParserFactory;

    private AttributesRepository $attributesFactory;

    private ReflectionPropertyDefinitionBuilder $propertyBuilder;

    private ReflectionMethodDefinitionBuilder $methodBuilder;

    /** @var array<string, ReflectionTypeResolver> */
    private array $typeResolver = [];

    public function __construct(TypeParserFactory $typeParserFactory, AttributesRepository $attributesFactory)
    {
        $this->typeParserFactory = $typeParserFactory;
        $this->attributesFactory = $attributesFactory;
        $this->propertyBuilder = new ReflectionPropertyDefinitionBuilder($attributesFactory);
        $this->methodBuilder = new ReflectionMethodDefinitionBuilder($attributesFactory);
    }

    public function for(ClassType $type): ClassDefinition
    {
        $reflection = Reflection::class($type->className());

        return new ClassDefinition(
            $type,
            $this->attributesFactory->for($reflection),
            new Properties(...$this->properties($type)),
            new Methods(...$this->methods($type)),
            $reflection->isFinal(),
            $reflection->isAbstract(),
        );
    }

    /**
     * @return list<PropertyDefinition>
     */
    private function properties(ClassType $type): array
    {
        return array_map(
            function (ReflectionProperty $property) use ($type) {
                $typeResolver = $this->typeResolver($type, $property->class);

                return $this->propertyBuilder->for($property, $typeResolver);
            },
            Reflection::class($type->className())->getProperties()
        );
    }

    /**
     * @return list<MethodDefinition>
     */
    private function methods(ClassType $type): array
    {
        $reflection = Reflection::class($type->className());
        $methods = $reflection->getMethods();

        // Because `ReflectionMethod::getMethods()` wont list the constructor if
        // it comes from a parent class AND is not public, we need to manually
        // fetch it and add it to the list.
        if ($reflection->hasMethod('__construct')) {
            $methods[] = $reflection->getMethod('__construct');
        }

        return array_map(function (ReflectionMethod $method) use ($type) {
            $typeResolver = $this->typeResolver($type, $method->class);

            return $this->methodBuilder->for($method, $typeResolver);
        }, $methods);
    }

    private function typeResolver(ClassType $type, string $targetClass): ReflectionTypeResolver
    {
        $typeKey = "{$type->toString()}/$targetClass";

        if (isset($this->typeResolver[$typeKey])) {
            return $this->typeResolver[$typeKey];
        }

        while ($type->className() !== $targetClass) {
            $type = $type->parent();
        }

        $generics = $type instanceof GenericType ? $type->generics() : [];
        $localAliases = $this->localTypeAliases($type);
        $importedAliases = $this->importedTypeAliases($type);

        $duplicates = [];
        $keys = [...array_keys($generics), ...array_keys($localAliases), ...array_keys($importedAliases)];

        foreach ($keys as $key) {
            $sameKeys = array_filter($keys, fn ($value) => $value === $key);

            if (count($sameKeys) > 1) {
                $duplicates[$key] = null;
            }
        }

        if (count($duplicates) > 0) {
            throw new ClassTypeAliasesDuplication($type->className(), ...array_keys($duplicates));
        }

        $advancedParser = $this->typeParserFactory->get(
            new ClassContextSpecification($type->className()),
            new AliasSpecification(Reflection::class($type->className())),
            new TypeAliasAssignerSpecification($generics + $localAliases + $importedAliases)
        );

        $nativeParser = $this->typeParserFactory->get(
            new ClassContextSpecification($type->className())
        );

        return $this->typeResolver[$typeKey] = new ReflectionTypeResolver($nativeParser, $advancedParser);
    }

    /**
     * @return array<string, Type>
     */
    private function localTypeAliases(ClassType $type): array
    {
        $reflection = Reflection::class($type->className());
        $rawTypes = Reflection::localTypeAliases($reflection);

        $typeParser = $this->typeParser($type);

        $types = [];

        foreach ($rawTypes as $name => $raw) {
            try {
                $types[$name] = $typeParser->parse($raw);
            } catch (InvalidType $exception) {
                $raw = trim($raw);

                $types[$name] = UnresolvableType::forLocalAlias($raw, $name, $type, $exception);
            }
        }

        return $types;
    }

    /**
     * @return array<string, Type>
     */
    private function importedTypeAliases(ClassType $type): array
    {
        $reflection = Reflection::class($type->className());
        $importedTypesRaw = Reflection::importedTypeAliases($reflection);

        $typeParser = $this->typeParser($type);

        $importedTypes = [];

        foreach ($importedTypesRaw as $class => $types) {
            try {
                $classType = $typeParser->parse($class);
            } catch (InvalidType) {
                throw new InvalidTypeAliasImportClass($type, $class);
            }

            if (! $classType instanceof ClassType) {
                throw new InvalidTypeAliasImportClassType($type, $classType);
            }

            $localTypes = $this->localTypeAliases($classType);

            foreach ($types as $importedType) {
                if (! isset($localTypes[$importedType])) {
                    throw new UnknownTypeAliasImport($type, $classType->className(), $importedType);
                }

                $importedTypes[$importedType] = $localTypes[$importedType];
            }
        }

        return $importedTypes;
    }

    private function typeParser(ClassType $type): TypeParser
    {
        $specs = [
            new ClassContextSpecification($type->className()),
            new AliasSpecification(Reflection::class($type->className())),
        ];

        if ($type instanceof GenericType) {
            $specs[] = new TypeAliasAssignerSpecification($type->generics());
        }

        return $this->typeParserFactory->get(...$specs);
    }
}
