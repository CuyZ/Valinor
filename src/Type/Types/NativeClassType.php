<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\GenericType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\Type;

use function array_map;
use function is_a;

/** @internal */
final class NativeClassType implements ClassType, GenericType
{
    public function __construct(
        /** @var class-string */
        private string $className,
        /** @var array<non-empty-string, Type> */
        private array $generics = [],
    ) {
        $this->className = ltrim($this->className, '\\');
    }

    public function className(): string
    {
        return $this->className;
    }

    public function generics(): array
    {
        return $this->generics;
    }

    public function accepts(mixed $value): bool
    {
        return $value instanceof $this->className;
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        return $node->instanceOf($this->className);
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof MixedType || $other instanceof UndefinedObjectType) {
            return true;
        }

        if ($other instanceof UnionType) {
            return $other->isMatchedBy($this);
        }

        if (! $other instanceof ObjectType) {
            return false;
        }

        return is_a($this->className, $other->className(), true);
    }

    public function traverse(): array
    {
        $types = [];

        foreach ($this->generics as $type) {
            $types[] = $type;

            if ($type instanceof CompositeType) {
                $types = [...$types, ...$type->traverse()];
            }
        }

        return $types;
    }

    public function nativeType(): NativeClassType
    {
        return new self($this->className);
    }

    public function toString(): string
    {
        return empty($this->generics)
            ? $this->className
            : $this->className . '<' . implode(', ', array_map(fn (Type $type) => $type->toString(), $this->generics)) . '>';
    }
}
