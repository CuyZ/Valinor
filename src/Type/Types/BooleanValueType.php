<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\BooleanType;
use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;

/** @api */
final class BooleanValueType implements BooleanType, FixedType
{
    private static self $true;

    private static self $false;

    private bool $value;

    /**
     * @codeCoverageIgnore
     */
    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function true(): self
    {
        return self::$true ??= new self(true);
    }

    public static function false(): self
    {
        return self::$false ??= new self(false);
    }

    public function accepts($value): bool
    {
        return $value === $this->value;
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        return $other === $this
            || $other instanceof MixedType
            || $other instanceof NativeBooleanType;
    }

    public function canCast($value): bool
    {
        if ($value === $this->value) {
            return true;
        }

        if ($this->value === true) {
            return $value === '1' || $value === 1 || $value === 'true';
        }

        return $value === '0' || $value === 0 || $value === 'false';
    }

    public function cast($value): bool
    {
        if (! $this->canCast($value)) {
            throw new CannotCastValue($value, $this);
        }

        return $this->value;
    }

    public function value(): bool
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value ? 'true' : 'false';
    }
}
