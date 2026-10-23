<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Parser\UnresolvableTypeFinderParser;
use CuyZ\Valinor\Type\Parser\VacantTypeAssignerParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

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
        private array $vacantTypes = [],
    ) {
        $this->parser = new UnresolvableTypeFinderParser(
            new VacantTypeAssignerParser($advancedParser, $vacantTypes),
        );
    }

    /**
     * @param array<non-empty-string, Type> $vacantTypes
     */
    public function withVacantTypes(array $vacantTypes): self
    {
        return new self($this->nativeParser, $this->advancedParser, [...$this->vacantTypes, ...$vacantTypes]);
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

    public function resolveNativeType(?ReflectionType $reflection): Type
    {
        if ($reflection === null) {
            return MixedType::get();
        }

        $type = $this->exportNativeType($reflection);

        return $this->nativeParser->parse($type);
    }

    private function exportNativeType(ReflectionType $type): string
    {
        if ($type instanceof ReflectionUnionType) {
            return implode('|', $type->getTypes());
        }
        if ($type instanceof ReflectionIntersectionType) {
            return implode('&', $type->getTypes());
        }

        /** @var ReflectionNamedType $type */
        $name = $type->getName();

        if ($name !== 'null' && $type->allowsNull() && $name !== 'mixed') {
            return $name . '|null';
        }

        return $name;
    }
}
