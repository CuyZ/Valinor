<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use BackedEnum;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\EnumType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\ValueDumper;
use Stringable;
use UnitEnum;

/** @api */
final class EnumValueType implements EnumType
{
    public function __construct(private UnitEnum $enum)
    {
    }

    public function className(): string
    {
        return $this->enum::class;
    }

    public function accepts(mixed $value): bool
    {
        return $value === $this->enum;
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof self) {
            return $this->enum === $other->enum;
        }

        if ($other instanceof NativeEnumType) {
            return $other->className() === $this->enum::class;
        }

        return $other instanceof MixedType || $other instanceof UndefinedObjectType;
    }

    public function canCast(mixed $value): bool
    {
        if ($value instanceof Stringable) {
            $value = (string)$value;
        }

        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        return $this->enum instanceof BackedEnum
            ? (string)$value === (string)$this->enum->value
            : $value === $this->enum->name;
    }

    public function cast(mixed $value): UnitEnum
    {
        assert($this->canCast($value));

        return $this->enum;
    }

    public function errorMessage(): ErrorMessage
    {
        $value = $this->enum instanceof BackedEnum
            ? $this->enum->value
            : $this->enum->name;

        return MessageBuilder::newError('Value {source_value} does not match {expected_value}.')
            ->withParameter('expected_value', ValueDumper::dump($value))
            ->build();
    }

    public function readableSignature(): string
    {
        return $this->enum instanceof BackedEnum
            ? (string)$this->enum->value
            : $this->enum->name;
    }

    public function toString(): string
    {
        return $this->enum::class . '::' . $this->enum->name;
    }
}
