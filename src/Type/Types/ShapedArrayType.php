<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\DumpableType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\InvalidShapedArrayUnsealedType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedArrayElementDuplicatedKey;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\VacantType;
use CuyZ\Valinor\Utility\Polyfill;

use function array_diff_key;
use function array_flip;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_shift;
use function array_values;
use function assert;
use function count;
use function function_exists;
use function implode;
use function in_array;
use function is_array;

/** @internal */
final class ShapedArrayType implements CompositeType, DumpableType
{
    private ?string $signature = null;

    public function __construct(
        /** @var array<ShapedArrayElement> */
        public readonly array $elements,
        public readonly bool $isUnsealed = false,
        public readonly ArrayType|VacantType|null $unsealedType = null,
    ) {}

    /**
     * @param array<ShapedArrayElement> $elements
     */
    public static function from(
        array $elements,
        bool $isUnsealed,
        Type|null $unsealedType = null,
    ): self {
        if ($unsealedType && ! $unsealedType instanceof ArrayType && ! $unsealedType instanceof VacantType) {
            throw new InvalidShapedArrayUnsealedType($unsealedType, ...$elements);
        }

        $sortedElements = [];

        $keys = [];

        foreach ($elements as $element) {
            $key = $element->key()->value();

            if (in_array($key, $keys, true)) {
                throw new ShapedArrayElementDuplicatedKey((string)$key);
            }

            $keys[] = $key;

            $sortedElements[$key] = $element;
        }

        return new self($sortedElements, $isUnsealed, $unsealedType);
    }

    public function hasUnsealedType(): bool
    {
        return $this->unsealedType !== null;
    }

    public function unsealedType(): ArrayType|VacantType
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

        foreach ($this->elements as $key => $element) {
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
                    ifFalse: Node::value($element->isOptional()),
                )->wrap();
            }, array_values($this->elements)),
        ];

        if ($this->isUnsealed) {
            $unsealedType = $this->unsealedType();

            if ($unsealedType instanceof VacantType) {
                return Node::value(false);
            }

            $elementsKeys = array_flip(array_keys($this->elements));

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

        if ($other instanceof self) {
            foreach ($this->elements as $key => $element) {
                if (isset($other->elements[$key]) && $element->type()->matches($other->elements[$key]->type())) {
                    continue;
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

        return false;
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        if (! $other instanceof self) {
            return $generics;
        }

        foreach ($this->elements as $key => $element) {
            if (! isset($other->elements[$key])) {
                continue;
            }

            $generics = $element->type()->inferGenericsFrom($other->elements[$key]->type(), $generics);
        }

        return $generics;
    }

    public function traverse(): array
    {
        $types = array_map(static fn (ShapedArrayElement $element) => $element->type(), array_values($this->elements));

        if (isset($this->unsealedType)) {
            $types[] = $this->unsealedType;
        }

        return $types;
    }

    public function replace(callable $callback): Type
    {
        $elements = array_map(
            fn (ShapedArrayElement $element) => new ShapedArrayElement(
                $element->key(),
                $callback($element->type()),
                $element->isOptional(),
                $element->attributes(),
            ),
            $this->elements,
        );

        $unsealedType = $this->unsealedType ? $callback($this->unsealedType) : null;

        return new self($elements, $this->isUnsealed, $unsealedType);
    }

    public function nativeType(): ArrayType
    {
        return ArrayType::native();
    }

    public function dumpParts(): iterable
    {
        $elements = $this->elements;

        yield 'array{';

        while ($element = array_shift($elements)) {
            $optional = $element->isOptional() ? '?' : '';
            yield $element->key()->toString() . "$optional: ";
            yield $element->type();

            if ($elements !== []) {
                yield ', ';
            }
        }

        yield '}';
    }

    public function toString(): string
    {
        return $this->signature ??= $this->buildSignature();
    }

    private function buildSignature(): string
    {
        $signature = 'array{';
        $signature .= implode(', ', array_map(static fn (ShapedArrayElement $element) => $element->toString(), $this->elements));

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
