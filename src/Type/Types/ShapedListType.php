<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\DumpableType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\InvalidShapedListUnsealedType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedListInvalidKey;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedListMandatoryAfterOptionalElement;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\VacantType;
use CuyZ\Valinor\Utility\Polyfill;

use function array_is_list;
use function array_key_exists;
use function array_map;
use function array_shift;
use function array_slice;
use function array_values;
use function assert;
use function count;
use function implode;
use function is_array;

/** @internal */
final class ShapedListType implements CompositeType, DumpableType
{
    private ?string $signature = null;

    public function __construct(
        /** @var array<ShapedArrayElement> */
        public readonly array $elements,
        public readonly ListType|VacantType|null $unsealedType = null,
    ) {}

    /**
     * @param list<ShapedArrayElement> $elements
     */
    public static function from(
        array $elements,
        Type|null $unsealedType = null,
    ): self {
        if ($unsealedType && ! $unsealedType instanceof ListType && ! $unsealedType instanceof VacantType) {
            throw new InvalidShapedListUnsealedType($unsealedType, ...$elements);
        }

        $sortedElements = [];
        $seenOptional = false;

        foreach ($elements as $index => $element) {
            if ($element->key()->value() !== $index) {
                throw new ShapedListInvalidKey($element->key(), $index);
            }

            if ($seenOptional && ! $element->isOptional()) {
                throw new ShapedListMandatoryAfterOptionalElement($index, ...$elements);
            }

            if ($element->isOptional()) {
                $seenOptional = true;
            }

            $sortedElements[$index] = $element;
        }

        return new self($sortedElements, $unsealedType);
    }

    public function isUnsealed(): bool
    {
        return $this->unsealedType !== null;
    }

    public function unsealedType(): ListType|VacantType
    {
        assert($this->unsealedType !== null);

        return $this->unsealedType ?? ListType::native();
    }

    public function accepts(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        if (! array_is_list($value)) {
            return false;
        }

        foreach ($this->elements as $key => $element) {
            if (array_key_exists($key, $value) ? ! $element->type()->accepts($value[$key]) : ! $element->isOptional()) {
                return false;
            }
        }

        if ($this->unsealedType) {
            $extra = array_slice($value, count($this->elements));

            return $this->unsealedType()->accepts($extra);
        }

        return count($value) <= count($this->elements);
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        $conditions = [
            Node::functionCall('is_array', [$node]),
            Node::functionCall('array_is_list', [$node]),
            ...array_map(function (ShapedArrayElement $element) use ($node) {
                $key = Node::value($element->key()->value());

                return Node::ternary(
                    condition: Node::functionCall('array_key_exists', [$key, $node]),
                    ifTrue: $element->type()->compiledAccept($node->key($key)),
                    ifFalse: Node::value($element->isOptional()),
                )->wrap();
            }, array_values($this->elements)),
        ];

        if ($this->unsealedType) {
            if ($this->unsealedType instanceof VacantType) {
                return Node::value(false);
            }

            $conditions[] = Node::class(Polyfill::class)->callStaticMethod(
                'array_all',
                [
                    Node::functionCall('array_slice', [$node, Node::value(count($this->elements))]),
                    Node::shortClosure(
                        $this->unsealedType->subType()->compiledAccept(Node::variable('item'))->wrap(),
                    )->witParameters(
                        Node::parameterDeclaration('item', 'mixed'),
                    ),
                ],
            );
        } else {
            $conditions[] = Node::functionCall('count', [$node])->isLessOrEqualsTo(Node::value(count($this->elements)));
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

            if ($other->unsealedType) {
                return $this->unsealedType
                    && $this->unsealedType->matches($other->unsealedType);
            }

            return true;
        }

        if ($other instanceof CompositeTraversableType) {
            if ($other->keyType() === ArrayKeyType::string()) {
                return false;
            }

            $subType = $other->subType();

            foreach ($this->elements as $element) {
                if (! $element->type()->matches($subType)) {
                    return false;
                }
            }

            if ($this->unsealedType && ! $this->unsealedType->matches($other)) {
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

        return new self($elements, $unsealedType);
    }

    public function nativeType(): ListType
    {
        return ListType::native();
    }

    public function dumpParts(): iterable
    {
        $elements = $this->elements;
        $hasOptional = $this->hasOptionalElements();

        yield 'list{';

        while ($element = array_shift($elements)) {
            if ($hasOptional) {
                yield $element->key()->value() . ($element->isOptional() ? '?: ' : ': ');
            }

            yield $element->type();

            if ($elements !== []) {
                yield ', ';
            }
        }

        if ($this->unsealedType) {
            if ($this->elements !== []) {
                yield ', ...';
            } else {
                yield '...';
            }

            if ($this->unsealedType !== ListType::native()) {
                yield $this->unsealedType;
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
        $hasOptional = $this->hasOptionalElements();

        $parts = array_map(
            static fn (ShapedArrayElement $element) => $hasOptional
                ? $element->key()->value() . ($element->isOptional() ? '?: ' : ': ') . $element->type()->toString()
                : $element->type()->toString(),
            $this->elements,
        );

        $signature = 'list{' . implode(', ', $parts);

        if ($this->unsealedType) {
            if ($this->elements !== []) {
                $signature .= ', ...';
            } else {
                $signature .= '...';
            }

            if ($this->unsealedType !== ListType::native()) {
                $signature .= $this->unsealedType->toString();
            }
        }

        $signature .= '}';

        return $signature;
    }

    private function hasOptionalElements(): bool
    {
        return Polyfill::array_any(
            $this->elements,
            static fn (ShapedArrayElement $element) => $element->isOptional(),
        );
    }
}
