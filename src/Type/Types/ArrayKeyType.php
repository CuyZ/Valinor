<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\DumpableType;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\InvalidArrayKey;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\VacantType;
use LogicException;

use function array_filter;
use function array_map;
use function array_shift;
use function array_values;
use function count;
use function implode;
use function in_array;
use function is_int;

/** @internal */
final class ArrayKeyType implements ScalarType, CompositeType, DumpableType
{
    private static self $default;

    private static self $integer;

    private static self $string;

    public function __construct(
        /** @var non-empty-list<IntegerType|StringType|VacantType> */
        public readonly array $types,
    ) {}

    public static function default(): self
    {
        return self::$default ??= new self([NativeIntegerType::get(), NativeStringType::get()]);
    }

    public static function integer(): self
    {
        return self::$integer ??= new self([NativeIntegerType::get()]);
    }

    public static function string(): self
    {
        return self::$string ??= new self([NativeStringType::get()]);
    }

    /**
     * @param non-empty-list<Type> $types
     */
    public static function from(array $types): self
    {
        if (count($types) === 1) {
            if ($types[0] instanceof NativeStringType) {
                return self::string();
            }

            if ($types[0] instanceof NativeIntegerType) {
                return self::integer();
            }
        }

        $invalidArrayKeys = array_filter(
            $types,
            static fn (Type $type) => ! $type instanceof IntegerType && ! $type instanceof StringType && ! $type instanceof VacantType,
        );

        if ($invalidArrayKeys !== []) {
            throw new InvalidArrayKey($invalidArrayKeys);
        }

        /** @var non-empty-list<IntegerType|StringType|VacantType> $types */
        return new self($types);
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
        foreach ($this->types as $type) {
            if (! $type->matches($other)) {
                return false;
            }
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

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        if (! $other instanceof self) {
            return $generics;
        }

        $selfTypes = UnionType::from(...$this->types);
        $otherTypes = UnionType::from(...$other->types);

        return $selfTypes->inferGenericsFrom($otherTypes, $generics);
    }

    public function canCast(mixed $value): bool
    {
        foreach ($this->types as $type) {
            if ($type instanceof ScalarType && $type->canCast($value)) {
                return true;
            }
        }

        return false;
    }

    public function cast(mixed $value): string|int
    {
        foreach ($this->types as $type) {
            if (! $type instanceof VacantType && $type->canCast($value)) {
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

    public function traverse(): array
    {
        return $this->types;
    }

    public function replace(callable $callback): Type
    {
        if (in_array($this, [self::default(), self::integer(), self::string()], true)) {
            return $this;
        }

        return self::from(
            array_map($callback, $this->types),
        );
    }

    public function nativeType(): Type
    {
        $types = [];

        foreach ($this->types as $type) {
            $types[$type->nativeType()->toString()] = $type->nativeType();
        }

        return UnionType::from(...array_values($types));
    }

    public function dumpParts(): iterable
    {
        $types = $this->types;

        while ($type = array_shift($types)) {
            yield $type;

            if ($types !== []) {
                yield '|';
            }
        }
    }

    public function toString(): string
    {
        if ($this === self::default()) {
            return 'array-key';
        }

        $signature = array_map(
            static fn (Type $type): string => $type->toString(),
            $this->types
        );

        return implode('|', $signature);
    }
}
