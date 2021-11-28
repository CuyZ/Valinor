<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;

use function implode;

final class IntersectionType implements CombiningType
{
    /** @var ObjectType[] */
    private array $types;

    private string $signature;

    public function __construct(ObjectType ...$types)
    {
        $this->types = $types;
        $this->signature = implode('&', $this->types);
    }

    public function accepts($value): bool
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

    /**
     * @return ObjectType[]
     */
    public function types(): array
    {
        return $this->types;
    }

    public function __toString(): string
    {
        return $this->signature;
    }
}
