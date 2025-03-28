<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Exception\ForbiddenMixedType;

use function array_map;
use function array_values;
use function implode;

/** @internal */
final class UnionType implements CombiningType
{
    /** @var non-empty-list<Type> */
    private array $types;

    private string $signature;

    public function __construct(Type $type, Type $otherType, Type ...$otherTypes)
    {
        $types = [$type, $otherType, ...$otherTypes];
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

        $this->types = $filteredTypes;
        $this->signature = implode('|', array_map(fn (Type $type) => $type->toString(), $this->types));
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
        if ($other instanceof self) {
            foreach ($this->types as $type) {
                if (! $other->isMatchedBy($type)) {
                    return false;
                }
            }

            return true;
        }

        foreach ($this->types as $type) {
            if ($type->matches($other)) {
                return true;
            }
        }

        return false;
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

    public function toString(): string
    {
        return $this->signature;
    }
}
