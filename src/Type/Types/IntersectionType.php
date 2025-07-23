<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;

use function array_values;
use function implode;

/** @internal */
final class IntersectionType implements CombiningType
{
    /** @var non-empty-list<ObjectType> */
    private array $types;

    private string $signature;

    public function __construct(ObjectType $type, ObjectType $otherType, ObjectType ...$otherTypes)
    {
        $this->types = [$type, $otherType, ...array_values($otherTypes)];
        $this->signature = implode('&', array_map(fn (Type $type) => $type->toString(), $this->types));
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
            fn (ObjectType $type) => $type->compiledAccept($node),
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
        $types = [];

        foreach ($this->types as $type) {
            $types[] = $type;

            if ($type instanceof CompositeType) {
                $types = [...$types, ...$type->traverse()];
            }
        }

        return $types;
    }

    /**
     * @return non-empty-list<ObjectType>
     */
    public function types(): array
    {
        return $this->types;
    }

    public function nativeType(): IntersectionType
    {
        return new self(
            ...array_map(
                static fn (ObjectType $type) => $type->nativeType(),
                $this->types,
            ),
        );
    }

    public function toString(): string
    {
        return $this->signature;
    }
}
