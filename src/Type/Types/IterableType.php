<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\DumpableType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Polyfill;
use Generator;

use function function_exists;
use function is_iterable;

/** @internal */
final class IterableType implements CompositeTraversableType, DumpableType
{
    private static self $native;

    public function __construct(
        private ArrayKeyType $keyType,
        private Type $subType,
    ) {}

    public static function native(): self
    {
        return self::$native ??= new self(ArrayKeyType::default(), MixedType::get());
    }

    public function accepts(mixed $value): bool
    {
        if (! is_iterable($value)) {
            return false;
        }

        if ($this === self::native()) {
            return true;
        }

        foreach ($value as $key => $item) {
            if (! $this->keyType->accepts($key)) {
                return false;
            }

            if (! $this->subType->accepts($item)) {
                return false;
            }
        }

        return true;
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        $condition = Node::logicalAnd(
            Node::functionCall('is_iterable', [$node]),
            Node::negate($node->instanceOf(Generator::class)),
        );

        if ($this === self::native()) {
            return $condition;
        }

        return $condition->and(Node::functionCall(function_exists('array_all') ? 'array_all' : Polyfill::class . '::array_all', [
            Node::functionCall('iterator_to_array', [$node]),
            Node::shortClosure(
                Node::logicalAnd(
                    $this->keyType->compiledAccept(Node::variable('key'))->wrap(),
                    $this->subType->compiledAccept(Node::variable('item'))->wrap(),
                ),
            )->witParameters(
                Node::parameterDeclaration('item', 'mixed'),
                Node::parameterDeclaration('key', 'mixed'),
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

        return $other instanceof self
            && $this->keyType->matches($other->keyType())
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

    public function nativeType(): IterableType
    {
        return self::native();
    }

    public function dumpParts(): iterable
    {
        yield 'iterable<';

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
            return 'iterable';
        }

        return $this->keyType === ArrayKeyType::default()
            ? "iterable<{$this->subType->toString()}>"
            : "iterable<{$this->keyType->toString()}, {$this->subType->toString()}>";
    }
}
