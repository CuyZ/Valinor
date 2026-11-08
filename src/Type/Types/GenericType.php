<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\VacantType;

use function array_filter;

/** @internal */
final readonly class GenericType implements VacantType
{
    public function __construct(
        /** @var non-empty-string */
        public string $symbol,
        public Type $innerType,
        public bool $covariant = false,
        public ?Type $default = null,
    ) {}

    public function accepts(mixed $value): bool
    {
        return $this->innerType->accepts($value);
    }

    public function compiledAccept(Node $node): Node
    {
        return $this->innerType->compiledAccept($node);
    }

    public function matches(Type $other): bool
    {
        return $this->innerType->matches($other);
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        if ($other->matches($this->innerType)) {
            return $generics->with($this, $other);
        }

        // For a union, the template is inferred from the members within its
        // bound: `T of int` is inferred as `int` from `int|string`.
        if ($other instanceof UnionType) {
            $members = array_filter(
                $other->types(),
                fn (Type $type) => $type->matches($this->innerType),
            );

            if ($members !== []) {
                return $generics->with($this, UnionType::from(...$members));
            }
        }

        return $generics;
    }

    public function nativeType(): Type
    {
        return $this->innerType->nativeType();
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    public function toString(): string
    {
        if ($this->innerType instanceof MixedType) {
            return $this->symbol;
        }

        return $this->symbol . ' of ' . $this->innerType->toString();
    }
}
