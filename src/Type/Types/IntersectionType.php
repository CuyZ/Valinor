<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Parser\Exception\Intersection\InvalidIntersectionElement;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\VacantType;

use function array_filter;
use function array_map;
use function implode;

/** @internal */
final class IntersectionType implements CombiningType
{
    /** @var non-empty-list<ObjectType|VacantType> */
    private array $types;

    /**
     * @no-named-arguments
     */
    public function __construct(ObjectType|VacantType $type, ObjectType|VacantType $otherType, ObjectType|VacantType ...$otherTypes)
    {
        $this->types = [$type, $otherType, ...$otherTypes];
    }

    /**
     * @no-named-arguments
     */
    public static function from(Type $type, Type $otherType, Type ...$otherTypes): self
    {
        $types = [$type, $otherType, ...$otherTypes];

        $invalidTypes = array_filter($types, static fn (Type $type) => ! $type instanceof ObjectType && ! $type instanceof VacantType);

        if ($invalidTypes !== []) {
            throw new InvalidIntersectionElement($types, $invalidTypes);
        }

        /** @var list<ObjectType|VacantType> $types */
        return new self(...$types);
    }

    public function accepts(mixed $value): bool
    {
        foreach ($this->types as $type) {
            if (! $type->accepts($value)) {
                return false;
            }
        }

        return true;
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        return Node::logicalAnd(...array_map(
            fn (Type $type) => $type->compiledAccept($node),
            $this->types,
        ));
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof MixedType) {
            return true;
        }

        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        foreach ($this->types as $type) {
            if (! $type->matches($other)) {
                return false;
            }
        }

        return true;
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        return $generics;
    }

    public function isMatchedBy(Type $other): bool
    {
        foreach ($this->types as $type) {
            if (! $other->matches($type)) {
                return false;
            }
        }

        return true;
    }

    public function traverse(): array
    {
        return $this->types;
    }

    public function replace(callable $callback): Type
    {
        return self::from(...array_map($callback, $this->types));
    }

    /**
     * @return non-empty-list<ObjectType|VacantType>
     */
    public function types(): array
    {
        return $this->types;
    }

    public function nativeType(): IntersectionType
    {
        // @phpstan-ignore argument.type
        return new self(...array_map(
            static fn (Type $type) => $type->nativeType(),
            $this->types,
        ));
    }

    public function toString(): string
    {
        return implode('&', array_map(static fn (Type $type) => $type->toString(), $this->types));
    }
}
