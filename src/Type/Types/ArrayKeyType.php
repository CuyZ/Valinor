<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;

use function is_int;

/** @internal */
final class ArrayKeyType implements Type
{
    private static self $integer;

    private static self $string;

    private static self $integerAndString;

    /** @var array<IntegerType|StringType> */
    private array $types;

    private string $signature;

    /**
     * @codeCoverageIgnore
     * @infection-ignore-all
     */
    private function __construct(IntegerType|StringType ...$types)
    {
        $this->types = $types;
        $this->signature = count($this->types) === 1
            ? $this->types[0]->toString()
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

    public function accepts(mixed $value): bool
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

    public function toString(): string
    {
        return $this->signature;
    }
}
