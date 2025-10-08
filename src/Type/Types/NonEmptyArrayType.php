<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\DumpableType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Polyfill;

use function function_exists;
use function is_array;

/** @internal */
final class NonEmptyArrayType implements CompositeTraversableType, DumpableType
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
        if (! is_array($value)) {
            return false;
        }

        if ($value === []) {
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

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        $condition = Node::logicalAnd(
            $node->different(Node::value([])),
            Node::functionCall('is_array', [$node]),
        );

        if ($this === self::native()) {
            return $condition;
        }

        return $condition->and(Node::functionCall(function_exists('array_all') ? 'array_all' : Polyfill::class . '::array_all', [
            $node,
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

        if (! $other instanceof CompositeTraversableType) {
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
        return ArrayType::native();
    }

    public function dumpParts(): iterable
    {
        yield 'non-empty-array<';

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
            return 'non-empty-array';
        }

        return $this->keyType === ArrayKeyType::default()
            ? "non-empty-array<{$this->subType->toString()}>"
            : "non-empty-array<{$this->keyType->toString()}, {$this->subType->toString()}>";
    }
}
