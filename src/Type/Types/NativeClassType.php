<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\GenericType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Type;

use function array_map;
use function assert;
use function get_parent_class;
use function is_a;

/** @internal */
final class NativeClassType implements ClassType, GenericType
{
    public function __construct(
        /** @var class-string */
        private string $className,
        /** @var array<string, Type> */
        private array $generics = [],
        private ?self $parent = null,
    ) {
        $this->className = ltrim($this->className, '\\');
    }

    /**
     * @param class-string $className
     */
    public static function for(string $className): self
    {
        $parentClass = get_parent_class($className);
        $parent = $parentClass ? self::for($parentClass) : null;

        return new self($className, [], $parent);
    }

    public function className(): string
    {
        return $this->className;
    }

    public function generics(): array
    {
        return $this->generics;
    }

    public function hasParent(): bool
    {
        return $this->parent instanceof self;
    }

    public function parent(): self
    {
        assert($this->parent instanceof self);

        return $this->parent;
    }

    public function accepts(mixed $value): bool
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

    public function traverse(): array
    {
        $types = [];

        foreach ($this->generics as $type) {
            $types[] = $type;

            if ($type instanceof CompositeType) {
                $types = [...$types, ...$type->traverse()];
            }
        }

        return $types;
    }

    public function toString(): string
    {
        return empty($this->generics)
            ? $this->className
            : $this->className . '<' . implode(', ', array_map(fn (Type $type) => $type->toString(), $this->generics)) . '>';
    }
}
