<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\InvalidArrayKey;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use LogicException;

use function is_int;

/** @internal */
final class ArrayKeyType implements ScalarType
{
    private static self $default;

    private static self $integer;

    private static self $string;

    /** @var non-empty-list<IntegerType|StringType> */
    private array $types;

    private string $signature;

    private function __construct(Type $type)
    {
        $types = $type instanceof UnionType
            ? [...$type->types()]
            : [$type];

        foreach ($types as $subType) {
            if (! $subType instanceof IntegerType && ! $subType instanceof StringType) {
                throw new InvalidArrayKey($subType);
            }
        }

        /** @var non-empty-list<IntegerType|StringType> $types */
        $this->types = $types;
        $this->signature = $type->toString();
    }

    public static function default(): self
    {
        if (!isset(self::$default)) {
            self::$default = new self(new UnionType(NativeIntegerType::get(), NativeStringType::get()));
            self::$default->signature = 'array-key';
        }

        return self::$default;
    }

    public static function integer(): self
    {
        return self::$integer ??= new self(NativeIntegerType::get());
    }

    public static function string(): self
    {
        return self::$string ??= new self(NativeStringType::get());
    }

    public static function from(Type $type): self
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

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        $conditions = [];

        foreach ($this->types as $type) {
            $condition = $type->compiledAccept($node);

            if ($type instanceof NativeStringType) {
                $condition = $condition->or(Node::functionCall('is_int', [$node]));
            }

            $conditions[] = $condition;
        }

        return Node::logicalOr(...$conditions);
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof MixedType) {
            return true;
        }

        if ($other instanceof ScalarConcreteType) {
            return true;
        }

        if ($other instanceof UnionType) {
            return $this->isMatchedBy($other);
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

    public function canCast(mixed $value): bool
    {
        foreach ($this->types as $type) {
            if ($type->canCast($value)) {
                return true;
            }
        }

        return false;
    }

    public function cast(mixed $value): string|int
    {
        foreach ($this->types as $type) {
            if ($type->canCast($value)) {
                return $type->cast($value);
            }
        }

        throw new LogicException();
    }

    public function errorMessage(): ErrorMessage
    {
        return MessageBuilder::newError('Value {source_value} is not a valid array key.')
            ->withCode('invalid_array_key')
            ->build();
    }

    public function nativeType(): Type
    {
        $types = [];

        foreach ($this->types as $type) {
            $types[$type->nativeType()->toString()] = $type->nativeType();
        }

        if (count($types) === 1) {
            return array_values($types)[0];
        }

        return new UnionType(...array_values($types));
    }

    public function toString(): string
    {
        return $this->signature;
    }
}
