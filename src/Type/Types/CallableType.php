<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Type;

use function array_map;
use function count;
use function implode;
use function is_callable;

/** @internal */
final class CallableType implements CompositeType
{
    private static self $default;

    public function __construct(
        /** @var list<Type> */
        public readonly array $parameters,
        public readonly Type $returnType,
    ) {}

    public static function default(): self
    {
        return self::$default ??= new self([], MixedType::get());
    }

    public function accepts(mixed $value): bool
    {
        return is_callable($value);
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        return Node::functionCall('is_callable', [$node]);
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof MixedType) {
            return true;
        }

        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        if (! $other instanceof self) {
            return false;
        }

        if (count($this->parameters) < count($other->parameters)) {
            return false;
        }

        foreach ($this->parameters as $key => $parameter) {
            if (! isset($other->parameters[$key])) {
                // @infection-ignore-all
                break;
            }

            if (! $parameter->matches($other->parameters[$key])) {
                return false;
            }
        }

        return $this->returnType->matches($other->returnType);
    }

    public function traverse(): array
    {
        return [...$this->parameters, $this->returnType];
    }

    public function replace(callable $callback): Type
    {
        return new self(
            array_map($callback, $this->parameters),
            $callback($this->returnType),
        );
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        return $generics;
    }

    public function nativeType(): Type
    {
        return $this;
    }

    public function toString(): string
    {
        if ($this === self::default()) {
            return 'callable';
        }

        // PHP8.5 use pipes
        $parameters = implode(
            ', ',
            array_map(static fn (Type $type): string => $type->toString(), $this->parameters),
        );

        return "callable($parameters): {$this->returnType->toString()}";
    }
}
