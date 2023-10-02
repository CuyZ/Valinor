<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\InvalidArrayKey;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;

use function is_int;

/** @internal */
final class ArrayKeyType implements Type
{
    private static self $default;

    private static self $integer;

    private static self $string;

    /** @var array<Type> */
    private array $types;

    private string $signature;

    private function __construct(Type $type)
    {
        $this->signature = $type->toString();
        $this->types = $type instanceof CombiningType
            ? [...$type->types()]
            : [$type];

        foreach ($this->types as $subType) {
            if (! $subType instanceof IntegerType && ! $subType instanceof StringType) {
                throw new InvalidArrayKey($subType);
            }
        }
    }

    public static function default(): self
    {
        return self::$default ??= new self(new UnionType(NativeIntegerType::get(), NativeStringType::get()));
    }

    public static function integer(): self
    {
        return self::$integer ??= new self(NativeIntegerType::get());
    }

    public static function string(): self
    {
        return self::$string ??= new self(NativeStringType::get());
    }

    public static function from(Type $type): ?self
    {
        return match (true) {
            $type instanceof self => $type,
            $type instanceof NativeIntegerType => self::integer(),
            $type instanceof NativeStringType => self::string(),
            default => new self($type),
        };
    }

    public function accepts(mixed $value): bool
    {
        foreach ($this->types as $type) {
            // If an array key can be evaluated as an integer, it will always be
            // cast to an integer, even if the actual key is a string.
            if (is_int($value) && $type instanceof NativeStringType) {
                return true;
            } elseif ($type->accepts($value)) {
                return true;
            }
        }

        return false;
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof MixedType) {
            return true;
        }

        if (! $other instanceof self) {
            return false;
        }

        foreach ($this->types as $type) {
            foreach ($other->types as $otherType) {
                if ($type->matches($otherType)) {
                    continue 2;
                }
            }

            return false;
        }

        return true;
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

    public function toString(): string
    {
        return $this->signature;
    }
}
