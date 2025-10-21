<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\DumpableType;
use CuyZ\Valinor\Type\Parser\Exception\Union\ForbiddenMixedType;
use CuyZ\Valinor\Type\Type;

use function array_filter;
use function array_map;
use function array_shift;
use function array_values;
use function implode;

/** @internal */
final class UnionType implements CombiningType, DumpableType
{
    /** @var non-empty-list<Type> */
    private array $types;

    /**
     * @no-named-arguments
     */
    public function __construct(Type $type, Type $otherType, Type ...$otherTypes)
    {
        $this->types = [$type, $otherType, ...$otherTypes];
    }

    /**
     * @no-named-arguments
     */
    public static function from(Type $type, Type ...$otherTypes): Type
    {
        if ($otherTypes === []) {
            return $type;
        }

        $types = [$type, ...$otherTypes];
        $filteredTypes = [];

        foreach ($types as $subType) {
            if ($subType instanceof self) {
                foreach ($subType->types as $anotherSubType) {
                    $filteredTypes[] = $anotherSubType;
                }

                continue;
            }

            if ($subType instanceof MixedType) {
                throw new ForbiddenMixedType();
            }

            $filteredTypes[] = $subType;
        }

        return new self(...$filteredTypes);
    }

    public function accepts(mixed $value): bool
    {
        foreach ($this->types as $type) {
            if ($type->accepts($value)) {
                return true;
            }
        }

        return false;
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        return Node::logicalOr(
            ...array_map(
                fn (Type $type) => $type->compiledAccept($node),
                $this->types,
            )
        );
    }

    public function matches(Type $other): bool
    {
        foreach ($this->types as $type) {
            if (! $type->matches($other)) {
                return false;
            }
        }

        return true;
    }

    public function isMatchedBy(Type $other): bool
    {
        foreach ($this->types as $type) {
            if ($other->matches($type)) {
                return true;
            }
        }

        return false;
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        $otherTypes = $other instanceof UnionType ? $other->types : [$other];
        $otherTypes = array_filter($otherTypes, fn (Type $type) => ! $type->matches($this));

        foreach ($otherTypes as $otherType) {
            foreach ($this->types as $type) {
                $generics = $type->inferGenericsFrom($otherType, $generics);
            }
        }

        return $generics;
    }

    public function traverse(): array
    {
        return $this->types;
    }

    public function replace(callable $callback): Type
    {
        return new self(...array_map($callback, $this->types));
    }

    public function types(): array
    {
        return $this->types;
    }

    public function nativeType(): UnionType
    {
        $subNativeTypes = [];

        foreach ($this->types as $type) {
            if (isset($subNativeTypes[$type->toString()])) {
                continue;
            }

            $subNativeTypes[$type->toString()] = $type->nativeType();
        }

        return new self(...array_values($subNativeTypes));
    }

    public function dumpParts(): iterable
    {
        $types = $this->types;

        while ($type = array_shift($types)) {
            yield $type;

            if ($types !== []) {
                yield '|';
            }
        }
    }

    public function toString(): string
    {
        return implode('|', array_map(static fn (Type $type) => $type->toString(), $this->types));
    }
}
