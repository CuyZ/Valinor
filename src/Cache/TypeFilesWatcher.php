<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache;

use Closure;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use CuyZ\Valinor\Utility\TypeHelper;
use ReflectionFunction;

use function array_filter;
use function array_map;
use function array_unique;
use function array_values;
use function is_string;

/** @internal */
final class TypeFilesWatcher
{
    public function __construct(
        private Settings $settings,
        private ClassDefinitionRepository $classDefinitionRepository,
    ) {}

    /**
     * This method returns a list of files in which are declared all types that
     * compose the given type. If the given type is composed of other types,
     * they are recursively resolved as well. If the type is a class, all
     * properties are also resolved.
     *
     * In addition, all callable that were given to the global settings are
     * checked, and their file names are added to the list.
     *
     * This file list can then be used by the `FileWatchingCache` to detect
     * changes to these files and invalidate the cache entries if needed.
     *
     * Example of returned value:
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
    public function for(Type $type): array
    {
        // Merging the files bound to settings and the files bound to the type
        $files = [
            ...array_map(
                fn (callable $callable) => (new ReflectionFunction(Closure::fromCallable($callable)))->getFileName(),
                $this->settings->callables(),
            ),
            ...$this->filesToWatch($type),
        ];

        // Removing the duplicates
        $files = array_unique($files);

        // Filtering the empty/invalid file names
        $files = array_filter($files, fn ($value) => is_string($value));

        /** @var list<non-empty-string> */
        return array_values($files);
    }

    /**
     * @param array<non-empty-string> $files
     * @return array<non-empty-string>
     */
    private function filesToWatch(Type $type, array $files = []): array
    {
        if (isset($files[$type->toString()])) {
            // Prevents infinite loop in case of circular references
            return [];
        }

        foreach (TypeHelper::traverseRecursively($type) as $subType) {
            $files = [...$files, ...$this->filesToWatch($subType, $files)];
        }

        if ($type instanceof ObjectType) {
            $fileName = Reflection::class($type->className())->getFileName();

            if (! $fileName) {
                return [];
            }

            $files[$type->toString()] = $fileName;

            $class = $this->classDefinitionRepository->for($type);

            foreach ($class->properties as $property) {
                $files = [...$files, ...$this->filesToWatch($property->type, $files)];
            }
        }

        return $files;
    }

}
