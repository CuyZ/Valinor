<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Utility\Polyfill;

use function function_exists;
use function is_array;

/** @internal */
final class NonEmptyListType implements CompositeTraversableType
{
    private static self $native;

    private Type $subType;

    private string $signature;

    public function __construct(Type $subType)
    {
        $this->subType = $subType;
        $this->signature = "non-empty-list<{$this->subType->toString()}>";
    }

    /**
     * @codeCoverageIgnore
     * @infection-ignore-all
     */
    public static function native(): self
    {
        if (! isset(self::$native)) {
            self::$native = new self(MixedType::get());
            self::$native->signature = 'non-empty-list';
        }

        return self::$native;
    }

    public function accepts(mixed $value): bool
    {
        if ($value === []) {
            return false;
        }

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

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        $condition = Node::logicalAnd(
            $node->different(Node::value([])),
            Node::functionCall('is_array', [$node]),
            Node::functionCall('array_is_list', [$node]),
        );

        if ($this === self::native()) {
            return $condition;
        }

        return $condition->and(Node::functionCall(function_exists('array_all') ? 'array_all' : Polyfill::class . '::array_all', [
            $node,
            Node::shortClosure(
                $this->subType->compiledAccept(Node::variable('item'))->wrap()
            )->witParameters(
                Node::parameterDeclaration('item', 'mixed'),
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

        if ($other instanceof ArrayType
            || $other instanceof NonEmptyArrayType
            || $other instanceof IterableType
        ) {
            return $other->keyType() !== ArrayKeyType::string()
                && $this->subType->matches($other->subType());
        }

        if ($other instanceof self || $other instanceof ListType) {
            return $this->subType->matches($other->subType());
        }

        return false;
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
        if ($this->subType instanceof CompositeType) {
            return [$this->subType, ...$this->subType->traverse()];
        }

        return [$this->subType];
    }

    public function nativeType(): ArrayType
    {
        return ArrayType::native();
    }

    public function toString(): string
    {
        return $this->signature;
    }
}
