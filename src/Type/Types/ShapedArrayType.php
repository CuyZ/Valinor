<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayElementDuplicatedKey;
use CuyZ\Valinor\Type\Type;

use CuyZ\Valinor\Utility\Polyfill;

use function array_diff_key;
use function array_key_exists;
use function array_map;
use function assert;
use function count;
use function implode;
use function in_array;
use function is_array;

/** @internal */
final class ShapedArrayType implements CompositeType
{
    /** @var list<ShapedArrayElement> */
    private array $elements;

    private bool $isUnsealed = false;

    private ?ArrayType $unsealedType = null;

    /**
     * @no-named-arguments
     */
    public function __construct(ShapedArrayElement ...$elements)
    {
        $this->elements = $elements;

        $keys = [];

        foreach ($this->elements as $elem) {
            $key = $elem->key()->value();

            if (in_array($key, $keys, true)) {
                throw new ShapedArrayElementDuplicatedKey((string)$key, $this->toString());
            }

            $keys[] = $key;
        }
    }

    /**
     * @no-named-arguments
     */
    public static function unsealed(ArrayType $unsealedType, ShapedArrayElement ...$elements): self
    {
        $self = new self(...$elements);
        $self->isUnsealed = true;
        $self->unsealedType = $unsealedType;

        return $self;
    }

    /**
     * @no-named-arguments
     */
    public static function unsealedWithoutType(ShapedArrayElement ...$elements): self
    {
        $self = new self(...$elements);
        $self->isUnsealed = true;

        return $self;
    }

    public function isUnsealed(): bool
    {
        return $this->isUnsealed;
    }

    public function hasUnsealedType(): bool
    {
        return $this->unsealedType !== null;
    }

    public function unsealedType(): ArrayType
    {
        assert($this->isUnsealed);

        return $this->unsealedType ?? ArrayType::native();
    }

    public function accepts(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        $elements = [];

        foreach ($this->elements as $element) {
            $key = $element->key()->value();

            $elements[$key] = null;

            if (array_key_exists($key, $value) ? ! $element->type()->accepts($value[$key]) : ! $element->isOptional()) {
                return false;
            }
        }

        if ($this->isUnsealed) {
            return $this->unsealedType()->accepts(array_diff_key($value, $elements));
        }

        return count($value) <= count($elements);
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        $conditions = [
            Node::functionCall('is_array', [$node]),
            ...array_map(function (ShapedArrayElement $element) use ($node) {
                $key = Node::value($element->key()->value());

                return Node::ternary(
                    condition: Node::functionCall('array_key_exists', [$key, $node]),
                    ifTrue: $element->type()->compiledAccept($node->key($key)),
                    ifFalse: Node::value($element->isOptional())
                )->wrap();
            }, $this->elements)
        ];

        if ($this->isUnsealed) {
            $unsealedType = $this->unsealedType();

            $elementsKeys = array_flip(
                array_map(fn (ShapedArrayElement $element) => $element->key()->value(), $this->elements)
            );

            $conditions[] = Node::functionCall(function_exists('array_all') ? 'array_all' : Polyfill::class . '::array_all', [
                Node::functionCall('array_diff_key', [$node, Node::value($elementsKeys)]),
                Node::shortClosure(
                    Node::logicalAnd(
                        $unsealedType->keyType()->compiledAccept(Node::variable('key'))->wrap(),
                        $unsealedType->subType()->compiledAccept(Node::variable('item'))->wrap(),
                    ),
                )->witParameters(
                    Node::parameterDeclaration('item', 'mixed'),
                    Node::parameterDeclaration('key', 'mixed'),
                ),
            ]);
        }

        return Node::logicalAnd(...$conditions);
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof MixedType) {
            return true;
        }

        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof CompositeTraversableType) {
            $keyType = $other->keyType();
            $subType = $other->subType();

            foreach ($this->elements as $element) {
                if (! $element->key()->matches($keyType)) {
                    return false;
                }

                if (! $element->type()->matches($subType)) {
                    return false;
                }
            }

            if ($this->isUnsealed && ! $this->unsealedType()->matches($other)) {
                return false;
            }

            return true;
        }

        if (! $other instanceof self) {
            return false;
        }

        foreach ($this->elements as $element) {
            foreach ($other->elements as $otherElement) {
                if ($element->key()->matches($otherElement->key())
                    && $element->type()->matches($otherElement->type())
                ) {
                    continue 2;
                }
            }

            if (! $element->isOptional()) {
                return false;
            }
        }

        if ($other->isUnsealed) {
            return $this->isUnsealed
                && $this->unsealedType()->matches($other->unsealedType());
        }

        return true;
    }

    public function traverse(): array
    {
        $types = [];

        foreach ($this->elements as $element) {
            $types[] = $type = $element->type();

            if ($type instanceof CompositeType) {
                $types = [...$types, ...$type->traverse()];
            }
        }

        if ($this->isUnsealed) {
            $types = [...$types, $this->unsealedType(), ...$this->unsealedType()->traverse()];
        }

        return $types;
    }

    /**
     * @return ShapedArrayElement[]
     */
    public function elements(): array
    {
        return $this->elements;
    }

    public function nativeType(): ArrayType
    {
        return ArrayType::native();
    }

    public function toString(): string
    {
        $signature = 'array{';
        $signature .= implode(', ', array_map(fn (ShapedArrayElement $element) => $element->toString(), $this->elements));

        if ($this->isUnsealed) {
            $signature .= ', ...';

            if ($this->unsealedType) {
                $signature .= $this->unsealedType->toString();
            }
        }

        $signature .= '}';

        return $signature;
    }
}
