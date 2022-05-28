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

use function get_class;

/** @api */
final class EnumValueType implements EnumType
{
    private UnitEnum $enum;

    public function __construct(UnitEnum $enum)
    {
        $this->enum = $enum;
    }

    public function className(): string
    {
        return get_class($this->enum);
    }

    public function generics(): array
    {
        return [];
    }

    public function accepts($value): bool
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
            return $other->className() === get_class($this->enum);
        }

        return $other instanceof MixedType || $other instanceof UndefinedObjectType;
    }

    public function canCast($value): bool
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

    public function cast($value): UnitEnum
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
        return get_class($this->enum) . '::' . $this->enum->name;
    }
}
