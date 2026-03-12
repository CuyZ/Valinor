<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\DumpableType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Polyfill;

use function CuyZ\Valinor\Compiler\{call, logicalAnd, param, shortClosure, variable};
use function is_array;

/** @internal */
final class ArrayType implements CompositeTraversableType, DumpableType
{
    private static self $native;

    public function __construct(
        private ArrayKeyType $keyType,
        private Type $subType,
        private bool $simple = false,
    ) {}

    public static function native(): self
    {
        return self::$native ??= new self(ArrayKeyType::default(), MixedType::get());
    }

    public static function simple(Type $type): self
    {
        return new self(ArrayKeyType::default(), $type, simple: true);
    }

    public function accepts(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        if ($this === self::native()) {
            return true;
        }

        return Polyfill::array_all(
            $value,
            fn (mixed $item, mixed $key) => $this->keyType->accepts($key) && $this->subType->accepts($item),
        );
    }

    public function compiledAccept(Node $node): Node
    {
        $condition = call('is_array', [$node]);

        if ($this === self::native()) {
            return $condition;
        }

        return $condition->and(call(Polyfill::array_all_name(), [
            $node,
            shortClosure(
                logicalAnd(
                    $this->keyType->compiledAccept(variable('key'))->wrap(),
                    $this->subType->compiledAccept(variable('item'))->wrap(),
                ),
                parameters: [
                    param('item', 'mixed'),
                    param('key', 'mixed'),
                ],
            ),
        ]));
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

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        if (! $other instanceof CompositeTraversableType) {
            return $generics;
        }

        $generics = $this->keyType->inferGenericsFrom($other->keyType(), $generics);

        return $this->subType->inferGenericsFrom($other->subType(), $generics);
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
        return [$this->keyType, $this->subType];
    }

    public function replace(callable $callback): Type
    {
        return new self(
            $callback($this->keyType),
            $callback($this->subType),
        );
    }

    public function nativeType(): ArrayType
    {
        return self::native();
    }

    public function dumpParts(): iterable
    {
        yield 'array<';

        if ($this->keyType !== ArrayKeyType::default()) {
            yield $this->keyType;
            yield ', ';
        }

        yield $this->subType;
        yield '>';
    }

    public function toString(): string
    {
        if ($this === self::native()) {
            return 'array';
        }

        if ($this->simple) {
            return $this->subType->toString() . '[]';
        }

        return $this->keyType === ArrayKeyType::default()
            ? "array<{$this->subType->toString()}>"
            : "array<{$this->keyType->toString()}, {$this->subType->toString()}>";
    }
}
