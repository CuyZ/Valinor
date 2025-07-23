<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Exception\InvalidUnionOfClassString;
use CuyZ\Valinor\Utility\IsSingleton;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use Stringable;

use function array_map;
use function assert;
use function is_string;

/** @internal */
final class ClassStringType implements StringType, CompositeType
{
    use IsSingleton;

    private ObjectType|UnionType|null $subType;

    private string $signature;

    public function __construct(ObjectType|UnionType|null $subType = null)
    {
        if ($subType instanceof UnionType) {
            foreach ($subType->types() as $type) {
                if (! $type instanceof ObjectType) {
                    throw new InvalidUnionOfClassString($subType);
                }
            }
        }

        $this->subType = $subType;
        $this->signature = $this->subType
            ? "class-string<{$this->subType->toString()}>"
            : 'class-string';
    }

    public function accepts(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        if (! $this->subType) {
            return Reflection::classOrInterfaceExists($value);
        }

        if ($this->subType instanceof ObjectType) {
            return is_a($value, $this->subType->className(), true);
        }

        foreach ($this->subType->types() as $type) {
            /** @var ObjectType $type */
            if (is_a($value, $type->className(), true)) {
                return true;
            }
        }

        return false;
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        $condition = Node::functionCall('is_string', [$node]);

        if (! $this->subType) {
            return $condition->and(Node::functionCall(Reflection::class . '::classOrInterfaceExists', [$node]));
        }

        if ($this->subType instanceof ObjectType) {
            return $condition->and(
                Node::functionCall('is_a', [
                    $node,
                    Node::value($this->subType->className()),
                    Node::value(true),
                ])
            );
        }

        $conditions = array_map(
            // @phpstan-ignore argument.type (We know it's an ObjectType)
            static fn (ObjectType $type) => Node::functionCall('is_a', [
                $node,
                Node::value($type->className()),
                Node::value(true),
            ]),
            $this->subType->types()
        );

        return $condition->and(Node::logicalOr(...$conditions)->wrap());
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof NativeStringType
            || $other instanceof NonEmptyStringType
            || $other instanceof ScalarConcreteType
            || $other instanceof MixedType
        ) {
            return true;
        }

        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        if (! $other instanceof self) {
            return false;
        }

        if (! $this->subType) {
            return true;
        }

        if (! $other->subType) {
            return true;
        }

        return $this->subType->matches($other->subType);
    }

    public function canCast(mixed $value): bool
    {
        return (is_string($value) || $value instanceof Stringable)
            && $this->accepts((string)$value);
    }

    public function cast(mixed $value): string
    {
        assert($this->canCast($value));

        return (string)$value; // @phpstan-ignore-line
    }

    public function errorMessage(): ErrorMessage
    {
        if ($this->subType) {
            return MessageBuilder::newError('Value {source_value} is not a valid class string of `{expected_class_type}`.')
                ->withCode('invalid_class_string')
                ->withParameter('expected_class_type', $this->subType->toString())
                ->build();
        }

        return MessageBuilder::newError('Value {source_value} is not a valid class string.')
            ->withCode('invalid_class_string')
            ->build();
    }

    public function subType(): ObjectType|UnionType|null
    {
        return $this->subType;
    }

    public function traverse(): array
    {
        if (! $this->subType) {
            return [];
        }

        if ($this->subType instanceof CompositeType) {
            return [$this->subType, ...$this->subType->traverse()];
        }

        return [$this->subType];
    }

    public function nativeType(): NativeStringType
    {
        return NativeStringType::get();
    }

    public function toString(): string
    {
        return $this->signature;
    }
}
