<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Parser\Exception\Union\InvalidClassStringElements;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\VacantType;
use CuyZ\Valinor\Utility\IsSingleton;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use Stringable;

use function array_filter;
use function array_map;
use function assert;
use function implode;
use function is_a;
use function is_string;

/** @internal */
final class ClassStringType implements StringType, CompositeType
{
    use IsSingleton;

    public function __construct(
        /** @var list<ObjectType|VacantType> */
        private array $subTypes = [],
    ) {}

    /**
     * @param list<Type> $subTypes
     */
    public static function from(array $subTypes): self
    {
        $invalidSubTypes = array_filter(
            $subTypes,
            static fn (Type $type) => ! $type instanceof ObjectType && ! $type instanceof VacantType
        );

        if ($invalidSubTypes !== []) {
            throw new InvalidClassStringElements($subTypes, $invalidSubTypes);
        }

        /** @var non-empty-list<ObjectType|VacantType> $subTypes */
        return new self($subTypes);
    }

    public function accepts(mixed $value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        if ($this->subTypes === []) {
            return Reflection::classOrInterfaceExists($value);
        }

        foreach ($this->subTypes as $type) {
            if ($type instanceof ObjectType && is_a($value, $type->className(), true)) {
                return true;
            }
        }

        return false;
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        $condition = Node::functionCall('is_string', [$node]);

        if ($this->subTypes === []) {
            return $condition->and(Node::functionCall(Reflection::class . '::classOrInterfaceExists', [$node]));
        }

        $conditions = [];

        foreach ($this->subTypes as $type) {
            if ($type instanceof ObjectType) {
                $conditions[] = Node::functionCall('is_a', [
                    $node,
                    Node::value($type->className()),
                    Node::value(true),
                ]);
            }
        }

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

        if ($other instanceof ArrayKeyType) {
            return $other->isMatchedBy($this);
        }

        if (! $other instanceof self) {
            return false;
        }

        foreach ($this->subTypes as $subType) {
            foreach ($other->subTypes as $otherSubType) {
                if (! $subType->matches($otherSubType)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        if (! $other instanceof self) {
            return $generics;
        }

        $selfTypes = UnionType::from(...$this->subTypes);
        $otherTypes = UnionType::from(...$other->subTypes);

        return $selfTypes->inferGenericsFrom($otherTypes, $generics);
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
        if ($this->subTypes === []) {
            return MessageBuilder::newError('Value {source_value} is not a valid class string.')
                ->withCode('invalid_class_string')
                ->build();
        }

        return MessageBuilder::newError('Value {source_value} is not a valid class string of `{expected_class_type}`.')
            ->withCode('invalid_class_string')
            ->withParameter('expected_class_type', $this->subTypesSignature())
            ->build();
    }

    /**
     * @return list<ObjectType|VacantType>
     */
    public function subTypes(): array
    {
        return $this->subTypes;
    }

    public function traverse(): array
    {
        return $this->subTypes;
    }

    public function replace(callable $callback): Type
    {
        return self::from(array_map($callback, $this->subTypes));
    }

    public function nativeType(): NativeStringType
    {
        return NativeStringType::get();
    }

    public function toString(): string
    {
        if ($this->subTypes === []) {
            return 'class-string';
        }

        return 'class-string<' . $this->subTypesSignature() . '>';
    }

    private function subTypesSignature(): string
    {
        return implode('|', array_map(
            static fn (Type $type) => $type->toString(),
            $this->subTypes,
        ));
    }
}
