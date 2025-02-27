<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Polyfill;

use function function_exists;
use function is_array;

/** @internal */
final class NonEmptyArrayType implements CompositeTraversableType
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
            ? "non-empty-array<{$subType->toString()}>"
            : "non-empty-array<{$keyType->toString()}, {$subType->toString()}>";
    }

    /**
     * @codeCoverageIgnore
     * @infection-ignore-all
     */
    public static function native(): self
    {
        if (! isset(self::$native)) {
            self::$native = new self(ArrayKeyType::default(), MixedType::get());
            self::$native->signature = 'non-empty-array';
        }

        return self::$native;
    }

    public function accepts(mixed $value): bool
    {
        if (! is_array($value)) {
            return false;
        }

        if ($value === []) {
            return false;
        }

        return ! Polyfill::array_any(
            $value,
            fn (mixed $item, mixed $key) => ! $this->keyType->accepts($key) || ! $this->subType->accepts($item),
        );
    }

    public function compiledAccept(CompliantNode $node): CompliantNode
    {
        return Node::logicalAnd(
            $node->different(Node::value([])),
            Node::functionCall('is_array', [$node]),
            Node::negate(
                Node::functionCall(function_exists('array_any') ? 'array_any' : Polyfill::class . '::array_any', [
                    $node,
                    Node::shortClosure(
                        Node::logicalOr(
                            Node::negate($this->keyType->compiledAccept(Node::variable('key'))->wrap()),
                            Node::negate($this->subType->compiledAccept(Node::variable('item'))->wrap()),
                        ),
                    )->witParameters(
                        Node::parameterDeclaration('item', 'mixed'),
                        Node::parameterDeclaration('key', 'mixed'),
                    ),
                ]),
            ),
        );
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

    public function toString(): string
    {
        return $this->signature;
    }
}
