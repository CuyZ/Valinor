<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;

/** @api */
final class InterfaceType implements ObjectType
{
    /** @var class-string */
    private string $interfaceName;

    /** @var array<string, Type> */
    private array $generics;

    /**
     * @param class-string $interfaceName
     * @param array<string, Type> $generics
     */
    public function __construct(string $interfaceName, array $generics = [])
    {
        $this->interfaceName = $interfaceName;
        $this->generics = $generics;
    }

    public function className(): string
    {
        return $this->interfaceName;
    }

    public function generics(): array
    {
        return $this->generics;
    }

    public function accepts($value): bool
    {
        return $value instanceof $this->interfaceName;
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof MixedType) {
            return true;
        }

        if ($other instanceof CombiningType) {
            return $other->isMatchedBy($this);
        }

        if (! $other instanceof ObjectType) {
            return false;
        }

        return is_a($other->className(), $this->interfaceName, true);
    }

    public function __toString(): string
    {
        return empty($this->generics)
            ? $this->interfaceName
            : $this->interfaceName . '<' . implode(', ', $this->generics) . '>';
    }
}
