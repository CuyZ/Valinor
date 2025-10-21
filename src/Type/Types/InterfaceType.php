<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\ObjectWithGenericType;
use CuyZ\Valinor\Type\Type;

use function array_map;
use function implode;
use function is_a;

/** @internal */
final class InterfaceType implements ObjectType, ObjectWithGenericType
{
    public function __construct(
        /** @var class-string */
        private string $interfaceName,
        /** @var list<Type> */
        private array $generics = []
    ) {}

    public function className(): string
    {
        return $this->interfaceName;
    }

    public function generics(): array
    {
        return $this->generics;
    }

    public function accepts(mixed $value): bool
    {
        return $value instanceof $this->interfaceName;
    }

    public function compiledAccept(ComplianceNode $node): ComplianceNode
    {
        return $node->instanceOf($this->interfaceName);
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof MixedType || $other instanceof UndefinedObjectType) {
            return true;
        }

        if ($other instanceof CombiningType) {
            return $other->isMatchedBy($this);
        }

        if (! $other instanceof ObjectType) {
            return false;
        }

        return is_a($this->interfaceName, $other->className(), true);
    }

    public function inferGenericsFrom(Type $other, Generics $generics): Generics
    {
        if (! $other instanceof self) {
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
            $this->interfaceName,
            array_map($callback, $this->generics),
        );
    }

    public function nativeType(): InterfaceType
    {
        return new self($this->interfaceName);
    }

    public function toString(): string
    {
        return empty($this->generics)
            ? $this->interfaceName
            : $this->interfaceName . '<' . implode(', ', array_map(static fn (Type $type) => $type->toString(), $this->generics)) . '>';
    }
}
