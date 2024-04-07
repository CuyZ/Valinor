<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

use function trim;

/** @internal */
final class ReflectionTypeResolver
{
    public function __construct(
        private TypeParser $nativeParser,
        private TypeParser $advancedParser,
    ) {}

    public function resolveType(?ReflectionType $native, ?string $docBlock): Type
    {
        if ($docBlock !== null) {
            $docBlock = trim($docBlock);

            return $this->parseType($docBlock, $this->advancedParser);
        }

        if ($native === null) {
            return MixedType::get();
        }

        $type = $this->exportNativeType($native);

        // When the type is a class, it may declare templates that must be
        // filled with generics. PHP does not handle generics natively, so we
        // need to make sure that no generics are left unassigned by parsing the
        // type using the advanced parser.
        return $this->parseType($type, $this->advancedParser);
    }

    public function resolveNativeType(?ReflectionType $reflection): Type
    {
        if ($reflection === null) {
            return MixedType::get();
        }

        $type = $this->exportNativeType($reflection);

        return $this->parseType($type, $this->nativeParser);
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

    private function parseType(string $raw, TypeParser $parser): Type
    {
        try {
            return $parser->parse($raw);
        } catch (InvalidType $exception) {
            return new UnresolvableType($raw, $exception->getMessage());
        }
    }
}
