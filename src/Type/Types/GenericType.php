<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\VacantType;

/** @internal */
final readonly class GenericType implements VacantType
{
    public function __construct(
        /** @var non-empty-string */
        public string $symbol,
        public Type $innerType,
    ) {}

    public function accepts(mixed $value): bool
    {
        return $this->innerType->accepts($value);
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
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
