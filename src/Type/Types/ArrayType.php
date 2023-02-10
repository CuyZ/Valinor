<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Type;

use function is_array;

/** @internal */
final class ArrayType implements CompositeTraversableType
{
    private static self $native;

    private ArrayKeyType $keyType;

    private Type $subType;

    private string $signature;

    public function __construct(ArrayKeyType $keyType, Type $subType)
    {
        $this->keyType = $keyType;
        $this->subType = $subType;
        $this->signature = $keyType === ArrayKeyType::default()
            ? "array<{$subType->toString()}>"
            : "array<{$keyType->toString()}, {$subType->toString()}>";
    }

    /**
     * @codeCoverageIgnore
     * @infection-ignore-all
     */
    public static function native(): self
    {
        if (! isset(self::$native)) {
            self::$native = new self(ArrayKeyType::default(), MixedType::get());
            self::$native->signature = 'array';
        }

        return self::$native;
    }

    public static function simple(Type $type): self
    {
        $instance = new self(ArrayKeyType::default(), $type);
        $instance->signature = $instance->subType->toString() . '[]';

        return $instance;
    }

    public function accepts(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $key => $item) {
            if (! $this->keyType->accepts($key)) {
                return false;
            }

            if (! $this->subType->accepts($item)) {
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

        if (! $other instanceof self && ! $other instanceof IterableType) {
            return false;
        }

        return $this->keyType->matches($other->keyType())
                && $this->subType->matches($other->subType());
    }

    public function keyType(): ArrayKeyType
    {
        return $this->keyType;
    }

    public function subType(): Type
    {
        return $this->subType;
    }

    public function traverse(): array
    {
        if ($this->subType instanceof CompositeType) {
            return [$this->subType, ...$this->subType->traverse()];
        }

        return [$this->subType];
    }

    public function toString(): string
    {
        return $this->signature;
    }
}
