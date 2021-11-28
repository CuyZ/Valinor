<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;

use function is_int;

final class ArrayKeyType implements ScalarType
{
    private static self $integer;

    private static self $string;

    private static self $integerAndString;

    /** @var array<IntegerType|StringType> */
    private array $types;

    private string $signature;

    /**
     * @param IntegerType|StringType ...$types
     * @codeCoverageIgnore
     * @infection-ignore-all
     */
    private function __construct(...$types)
    {
        $this->types = $types;
        $this->signature = count($this->types) === 1
            ? (string)$this->types[0]
            : 'array-key';
    }

    public static function default(): self
    {
        return self::$integerAndString ??= new self(NativeIntegerType::get(), NativeStringType::get());
    }

    public static function integer(): self
    {
        return self::$integer ??= new self(NativeIntegerType::get());
    }

    public static function string(): self
    {
        return self::$string ??= new self(NativeStringType::get());
    }

    public function accepts($value): bool
    {
        // If an array key can be evaluated as an integer, it will always be
        // cast to an integer, even if the actual key is a string.
        if (is_int($value)) {
            return true;
        }

        foreach ($this->types as $type) {
            if ($type->accepts($value)) {
                return true;
            }
        }

        return false;
    }

    public function matches(Type $other): bool
    {
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

    public function canCast($value): bool
    {
        foreach ($this->types as $type) {
            if ($type->canCast($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return int|string
     */
    public function cast($value)
    {
        if ($this->accepts($value)) {
            /** @var int|string $value */
            return $value;
        }

        foreach ($this->types as $type) {
            if ($type->canCast($value)) {
                return $type->cast($value);
            }
        }

        throw new CannotCastValue($value, $this);
    }

    public function __toString(): string
    {
        return $this->signature;
    }
}
