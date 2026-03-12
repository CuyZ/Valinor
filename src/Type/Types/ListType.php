<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\DumpableType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Polyfill;

use function array_is_list;
use function CuyZ\Valinor\Compiler\{call, logicalAnd, param, shortClosure, variable};
use function is_array;

/** @internal */
final class ListType implements CompositeTraversableType, DumpableType
{
    private static self $native;

    public function __construct(private Type $subType) {}

    public static function native(): self
    {
        return self::$native ??= new self(MixedType::get());
    }

    public function accepts(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        if (! array_is_list($value)) {
            return false;
        }

        if ($this === self::native()) {
            return true;
        }

        return Polyfill::array_all(
            $value,
            fn (mixed $item) => $this->subType->accepts($item),
        );
    }

    public function compiledAccept(Node $node): Node
    {
        $condition = logicalAnd(
            call('is_array', [$node]),
            call('array_is_list', [$node]),
        );

        if ($this === self::native()) {
            return $condition;
        }

        return $condition->and(call(Polyfill::array_all_name(), [
            $node,
            shortClosure(
                $this->subType->compiledAccept(variable('item'))->wrap(),
                parameters: [param('item', 'mixed')],
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

        if ($other instanceof ArrayType || $other instanceof IterableType) {
            return $other->keyType() !== ArrayKeyType::string()
                && $this->subType->matches($other->subType());
        }

        if ($other instanceof self) {
            return $this->subType->matches($other->subType);
        }

        return false;
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        if (! $other instanceof CompositeTraversableType) {
            return $generics;
        }

        return $this->subType->inferGenericsFrom($other->subType(), $generics);
    }

    public function keyType(): ArrayKeyType
    {
        return ArrayKeyType::integer();
    }

    public function subType(): Type
    {
        return $this->subType;
    }

    public function traverse(): array
    {
        return [$this->subType];
    }

    public function replace(callable $callback): Type
    {
        return new self($callback($this->subType));
    }

    public function nativeType(): ArrayType
    {
        return ArrayType::native();
    }

    public function dumpParts(): iterable
    {
        yield 'list<';
        yield $this->subType;
        yield '>';
    }

    public function toString(): string
    {
        if ($this === self::native()) {
            return 'list';
        }

        return "list<{$this->subType->toString()}>";
    }
}
