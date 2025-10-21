<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\ObjectWithGenericType;
use CuyZ\Valinor\Type\Type;

use function array_map;
use function implode;
use function is_a;
use function ltrim;

/** @internal */
final class NativeClassType implements ClassType, ObjectWithGenericType
{
    public function __construct(
        /** @var class-string */
        private string $className,
        /** @var list<Type> */
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

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        if (! $other instanceof self || $this->className !== $other->className) {
            return $generics;
        }

        foreach ($this->generics as $key => $classGenerics) {
            if (isset($other->generics[$key])) {
                $generics = $classGenerics->inferGenericsFrom($other->generics[$key], $generics);
            }
        }

        return $generics;
    }

    public function traverse(): array
    {
        return $this->generics;
    }

    public function replace(callable $callback): Type
    {
        return new self(
            $this->className,
            array_map($callback, $this->generics),
        );
    }

    public function nativeType(): NativeClassType
    {
        return new self($this->className);
    }

    public function toString(): string
    {
        return empty($this->generics)
            ? $this->className
            : $this->className . '<' . implode(', ', array_map(static fn (Type $type) => $type->toString(), $this->generics)) . '>';
    }
}
