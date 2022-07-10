<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Type;

use function is_a;

/** @internal */
final class ClassType implements ObjectType, CompositeType
{
    /** @var class-string */
    private string $className;

    /** @var array<string, Type> */
    private array $generics;

    /**
     * @param class-string $className
     * @param array<string, Type> $generics
     */
    public function __construct(string $className, array $generics = [])
    {
        $this->className = ltrim($className, '\\'); // @phpstan-ignore-line
        $this->generics = $generics;
    }

    public function className(): string
    {
        return $this->className;
    }

    public function generics(): array
    {
        return $this->generics;
    }

    public function accepts($value): bool
    {
        return $value instanceof $this->className;
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof MixedType || $other instanceof UndefinedObjectType) {
            return true;
        }

        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        if (! $other instanceof ObjectType) {
            return false;
        }

        return is_a($this->className, $other->className(), true);
    }

    public function traverse(): iterable
    {
        foreach ($this->generics as $type) {
            yield $type;

            if ($type instanceof CompositeType) {
                yield from $type->traverse();
            }
        }
    }

    public function __toString(): string
    {
        return empty($this->generics)
            ? $this->className
            : $this->className . '<' . implode(', ', $this->generics) . '>';
    }
}
