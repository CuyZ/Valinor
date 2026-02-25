<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache;

use CuyZ\Valinor\Definition\Attributes;
use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use CuyZ\Valinor\Utility\TypeHelper;

use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;
use function array_values;
use function is_string;

/** @internal */
final class TypeFilesWatcher
{
    public function __construct(
        private Settings $settings,
        private ClassDefinitionRepository $classDefinitionRepository,
        private FunctionDefinitionRepository $functionDefinitionRepository,
    ) {}

    /**
     * This method returns a list of files in which are declared all types that
     * compose the given type. If the given type is composed of other types,
     * they are recursively resolved as well.
     *
     * This file list can then be used by the `FileWatchingCache` to detect
     * changes to these files and invalidate the cache entries if needed.
     *
     * If the type is a class, will be resolved:
     * - The class attributes
     * - The properties attributes and types
     * - The methods attributes and return types
     * - The method parameters attributes and types
     *
     * If the type is a callable, will be resolved:
     * - The callable attributes and return type
     * - The callable parameters attributes and types
     *
     * In addition, all callables that were given to the global settings are
     * checked, and their file names are added to the list.
     *
     * Example of a returned value:
     *
     * ```
     * [
     *     // An entity representing a user
     *     '/root/path/src/Domain/User/User.php',
     *
     *     // The user has an `$email` property and a `$phoneNumber` property
     *     '/root/path/src/Domain/Shared/Email.php',
     *     '/root/path/src/Domain/Shared/PhoneNumber.php',
     *
     *     // The settings contain a custom constructor defined in a file
     *     '/root/path/src/Infrastructure/Mapper/CustomConstructor.php',
     *
     *     // The settings contain a custom exception filter
     *     '/root/path/src/Infrastructure/Mapper/CustomExceptionFilter.php',
     *
     *     // And maybe more…
     * ];
     * ```
     *
     * @return list<non-empty-string>
     */
    public function for(Type|callable $item): array
    {
        // Merging the files bound to settings and the files bound to the type
        $files = array_merge(...array_map(
            $this->forCallable(...),
            $this->settings->callables(),
        ));

        if ($item instanceof Type) {
            $files = [...$files, ...$this->forType($item)];
        } else {
            $files = [...$files, ...$this->forCallable($item)];
        }

        // Removing the duplicates
        $files = array_unique($files);

        // Filtering the empty/invalid file names
        $files = array_filter($files, is_string(...));

        /** @var list<non-empty-string> */
        return array_values($files);
    }

    /**
     * @return list<non-empty-string>
     */
    private function forCallable(callable $callable): array
    {
        $definition = $this->functionDefinitionRepository->for($callable);
        $visited = [];
        $files = [];

        if ($definition->fileName && $definition->class !== Settings::class) {
            $files = [$definition->fileName];
        }

        $returnFiles = $this->forType($definition->returnType, $visited);
        $attributeFiles = $this->forAttributes($definition->attributes, $visited);

        $files = [...$files, ...$returnFiles, ...$attributeFiles];

        foreach ($definition->parameters as $parameter) {
            $parameterFiles = $this->forType($parameter->type, $visited);
            $attributeFiles = $this->forAttributes($parameter->attributes, $visited);

            $files = [...$files, ...$parameterFiles, ...$attributeFiles];
        }

        return $files;
    }

    /**
     * @param array<string, true> $visited
     * @return list<non-empty-string>
     */
    private function forType(Type $type, array &$visited = []): array
    {
        if (isset($visited[$type->toString()])) {
            // Prevents infinite loop in case of circular references
            return [];
        }

        $visited[$type->toString()] = true;

        $files = [];

        foreach (TypeHelper::traverseRecursively($type) as $subType) {
            $files = [...$files, ...$this->forType($subType, $visited)];
        }

        if ($type instanceof ObjectType) {
            $class = $this->classDefinitionRepository->for($type);
            $classFiles = $this->forClass($class, $visited);

            $files = [...$files, ...$classFiles];
        }

        return $files;
    }

    /**
     * @param array<string, true> $visited
     * @return list<non-empty-string>
     */
    private function forClass(ClassDefinition $class, array &$visited): array
    {
        $objectFiles = $this->traverseObjectInheritanceFileNames($class->name);

        $attributeFiles = $this->forAttributes($class->attributes, $visited);

        $files = [...$objectFiles, ...$attributeFiles];

        foreach ($class->properties as $property) {
            $propertyFiles = $this->forType($property->type, $visited);
            $attributeFiles = $this->forAttributes($property->attributes, $visited);

            $files = [...$files, ...$propertyFiles, ...$attributeFiles];
        }

        foreach ($class->methods as $method) {
            $returnFiles = $this->forType($method->returnType, $visited);
            $attributeFiles = $this->forAttributes($method->attributes, $visited);

            $files = [...$files, ...$returnFiles, ...$attributeFiles];

            foreach ($method->parameters as $parameter) {
                $parameterFiles = $this->forType($parameter->type, $visited);
                $attributeFiles = $this->forAttributes($parameter->attributes, $visited);

                $files = [...$files, ...$parameterFiles, ...$attributeFiles];
            }
        }

        return $files;
    }

    /**
     * @param array<string, true> $visited
     * @return list<non-empty-string>
     */
    private function forAttributes(Attributes $attributes, array &$visited): array
    {
        $files = [];

        foreach ($attributes as $attribute) {
            $files = [...$files, ...$this->forType($attribute->class->type, $visited)];
        }

        return $files;
    }

    /**
     * @param class-string $className
     * @return list<non-empty-string>
     */
    private function traverseObjectInheritanceFileNames(string $className): array
    {
        $reflection = Reflection::class($className);

        $fileNames = [];

        do {
            $fileName = $reflection->getFileName();

            if (is_string($fileName)) {
                $fileNames[] = $fileName;
            }
        } while ($reflection = $reflection->getParentClass());

        return $fileNames;
    }
}
