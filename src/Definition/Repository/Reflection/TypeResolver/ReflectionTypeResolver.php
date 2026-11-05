<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Parser\UnresolvableTypeFinderParser;
use CuyZ\Valinor\Type\Parser\VacantTypeAssignerParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Type\VacantType;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

use function array_count_values;
use function array_diff_key;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function implode;
use function trim;

/** @internal */
final class ReflectionTypeResolver
{
    private TypeParser $parser;

    public function __construct(
        private TypeParser $nativeParser,
        private TypeParser $advancedParser,
        /** @var array<non-empty-string, Type> */
        private array $generics = [],
        /** @var array<non-empty-string, Type> */
        private array $localTypes = [],
        /** @var array<non-empty-string, Type> */
        private array $importedTypes = [],
    ) {
        $this->parser = new UnresolvableTypeFinderParser(
            new VacantTypeAssignerParser($advancedParser, $this->vacantTypes()),
        );
    }

    /**
     * @param array<non-empty-string, Type> $generics
     */
    public function withGenerics(array $generics): self
    {
        return new self(
            $this->nativeParser,
            $this->advancedParser,
            [...$this->generics, ...$generics],
            array_diff_key($this->localTypes, $generics),
            array_diff_key($this->importedTypes, $generics),
        );
    }

    public function resolveType(?ReflectionType $native, ?string $docBlock): Type
    {
        if ($docBlock !== null) {
            $docBlock = trim($docBlock);

            return $this->parser->parse($docBlock);
        }

        if ($native === null) {
            return MixedType::get();
        }

        $type = $this->exportNativeType($native);

        // When the type is a class, it may declare templates that must be
        // filled with generics. PHP does not handle generics natively, so we
        // need to make sure that no generics are left unassigned by parsing the
        // type using the advanced parser.
        return $this->parser->parse($type);
    }

    public function isTemplate(?string $docBlock): bool
    {
        if ($docBlock === null || $this->generics === []) {
            return false;
        }

        $type = $this->advancedParser->parse($docBlock);

        return $type instanceof VacantType
            && array_key_exists($type->symbol(), $this->generics);
    }

    public function resolveNativeType(?ReflectionType $reflection): Type
    {
        if ($reflection === null) {
            return MixedType::get();
        }

        $type = $this->exportNativeType($reflection);

        return $this->nativeParser->parse($type);
    }

    /**
     * @return array<non-empty-string, Type>
     */
    private function vacantTypes(): array
    {
        $vacantTypes = [...$this->generics, ...$this->localTypes, ...$this->importedTypes];

        $keys = [...array_keys($this->generics), ...array_keys($this->localTypes), ...array_keys($this->importedTypes)];

        // PHP8.5 use pipes
        $aliasCollision = array_filter(
            array_count_values($keys),
            static fn (int $count) => $count > 1
        );

        foreach ($aliasCollision as $alias => $numberOfCollisions) {
            /** @var non-empty-string $alias */
            $vacantTypes[$alias] = UnresolvableType::forClassTypeAliasesCollision($alias, $numberOfCollisions);
        }

        return $vacantTypes;
    }

    private function exportNativeType(ReflectionType $type): string
    {
        if ($type instanceof ReflectionUnionType) {
            return implode('|', array_map($this->exportNativeType(...), $type->getTypes()));
        }

        if ($type instanceof ReflectionIntersectionType) {
            return implode('&', array_map($this->exportNativeType(...), $type->getTypes()));
        }

        /** @var ReflectionNamedType $type */
        $name = $type->getName();

        if (! $type->isBuiltin() && $name !== 'self' && $name !== 'static') {
            $name = '\\' . $name;
        }

        if ($name !== 'null' && $type->allowsNull() && $name !== 'mixed') {
            return $name . '|null';
        }

        return $name;
    }
}
