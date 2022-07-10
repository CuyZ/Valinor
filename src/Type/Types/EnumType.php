<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use UnitEnum;

use function in_array;

/** @internal */
final class EnumType implements ObjectType
{
    /** @var class-string<UnitEnum> */
    private string $enumName;

    /**
     * @param class-string<UnitEnum> $enumName
     */
    public function __construct(string $enumName)
    {
        $this->enumName = $enumName;
    }

    /**
     * @return class-string<UnitEnum>
     */
    public function className(): string
    {
        return $this->enumName;
    }

    public function generics(): array
    {
        return [];
    }

    public function accepts($value): bool
    {
        return in_array($value, ($this->enumName)::cases(), true);
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof CombiningType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof self) {
            return $other->enumName === $this->enumName;
        }

        return $other instanceof UndefinedObjectType
            || $other instanceof MixedType;
    }

    public function __toString(): string
    {
        return $this->enumName;
    }
}
