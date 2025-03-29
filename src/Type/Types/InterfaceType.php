<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\GenericType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;

use function array_map;

/** @internal */
final class InterfaceType implements ObjectType, GenericType
{
    public function __construct(
        /** @var class-string */
        private string $interfaceName,
        /** @var array<non-empty-string, Type> */
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

    public function nativeType(): InterfaceType
    {
        return new self($this->interfaceName);
    }

    public function toString(): string
    {
        return empty($this->generics)
            ? $this->interfaceName
            : $this->interfaceName . '<' . implode(', ', array_map(fn (Type $type) => $type->toString(), $this->generics)) . '>';
    }
}
