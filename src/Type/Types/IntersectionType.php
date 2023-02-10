<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Type;

use function implode;

/** @internal */
final class IntersectionType implements CombiningType
{
    /** @var ObjectType[] */
    private array $types;

    private string $signature;

    public function __construct(ObjectType ...$types)
    {
        $this->types = $types;
        $this->signature = implode('&', array_map(fn (Type $type) => $type->toString(), $types));
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
     * @return ObjectType[]
     */
    public function types(): array
    {
        return $this->types;
    }

    public function toString(): string
    {
        return $this->signature;
    }
}
