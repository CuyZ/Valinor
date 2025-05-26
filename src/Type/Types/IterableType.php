<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Polyfill;
use Generator;

use function is_iterable;

/** @internal */
final class IterableType implements CompositeTraversableType
{
    private static self $native;

    private ArrayKeyType $keyType;

    private Type $subType;

    private string $signature;

    public function __construct(ArrayKeyType $keyType, Type $subType)
    {
        $this->keyType = $keyType;
        $this->subType = $subType;
        $this->signature = $keyType === ArrayKeyType::default()
            ? "iterable<{$this->subType->toString()}>"
            : "iterable<{$this->keyType->toString()}, {$this->subType->toString()}>";
    }

    public static function native(): self
    {
        if (! isset(self::$native)) {
            self::$native = new self(ArrayKeyType::default(), MixedType::get());
            self::$native->signature = 'iterable';
        }

        return self::$native;
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

        // @infection-ignore-all
        $iteratorToArray = PHP_VERSION_ID >= 8_02_00
            ? Node::functionCall('iterator_to_array', [$node])
            : Node::ternary(
                condition: Node::functionCall('is_array', [$node]),
                ifTrue: $node,
                ifFalse: Node::functionCall('iterator_to_array', [$node]),
            );

        return $condition->and(Node::functionCall(function_exists('array_all') ? 'array_all' : Polyfill::class . '::array_all', [
            $iteratorToArray,
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

        return $other instanceof CompositeTraversableType
            && $this->keyType->matches($other->keyType())
            && $this->subType->matches($other->subType());
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
        if ($this->subType instanceof CompositeType) {
            return [$this->subType, ...$this->subType->traverse()];
        }

        return [$this->subType];
    }

    public function nativeType(): IterableType
    {
        return self::native();
    }

    public function toString(): string
    {
        return $this->signature;
    }
}
