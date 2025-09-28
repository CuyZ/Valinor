<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\Reflection\Annotations;

use function current;
use function key;
use function next;

/** @internal */
final class ClassImportedTypeAliasResolver
{
    private ClassLocalTypeAliasResolver $localTypeAliasResolver;

    public function __construct(private TypeParserFactory $typeParserFactory)
    {
        $this->localTypeAliasResolver = new ClassLocalTypeAliasResolver($this->typeParserFactory);
    }

    /**
     * @return array<non-empty-string, Type>
     */
    public function resolveImportedTypeAliases(ObjectType $type): array
    {
        $importedTypesRaw = $this->extractImportedAliasesFromDocBlock($type->className());

        if ($importedTypesRaw === []) {
            return [];
        }

        $typeParser = $this->typeParserFactory->buildAdvancedTypeParserForClass($type->className());

        $importedTypes = [];

        foreach ($importedTypesRaw as $class => $types) {
            $classType = $typeParser->parse($class);

            if (! $classType instanceof ObjectType) {
                foreach ($types as $importedType) {
                    $importedTypes[$importedType] = UnresolvableType::forInvalidAliasImportClassType($type->className(), $types[0], $class);
                }

                continue;
            }

            $localTypes = $this->localTypeAliasResolver->resolveLocalTypeAliases($classType);

            foreach ($types as $importedType) {
                if (isset($localTypes[$importedType])) {
                    $importedTypes[$importedType] = $localTypes[$importedType];
                } else {
                    $importedTypes[$importedType] = UnresolvableType::forUnknownTypeAliasImport($type, $classType->className(), $importedType);
                }

            }
        }

        return $importedTypes;
    }

    /**
     * @param class-string $className
     * @return array<non-empty-string, non-empty-list<non-empty-string>>
     */
    private function extractImportedAliasesFromDocBlock(string $className): array
    {
        $importedAliases = [];

        $annotations = Annotations::forImportTypes($className);

        foreach ($annotations as $annotation) {
            $tokens = $annotation->filtered();

            $name = current($tokens);
            $from = next($tokens);

            if ($from !== 'from') {
                continue;
            }

            next($tokens);

            $key = key($tokens);

            // @phpstan-ignore identical.alwaysFalse (Somehow PHPStan does not properly infer the key)
            if ($key === null) {
                continue;
            }

            $class = $annotation->allAfter($key);

            $importedAliases[$class][] = $name;
        }

        return $importedAliases;
    }
}
