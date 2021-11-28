<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Types;

use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use UnitEnum;

use function in_array;

final class EnumType implements ObjectType
{
    private ClassSignature $signature;

    /**
     * @param class-string<UnitEnum> $enumName
     */
    public function __construct(string $enumName)
    {
        $this->signature = new ClassSignature($enumName);
    }

    public function accepts($value): bool
    {
        $enumName = $this->signature->className();

        return in_array($value, $enumName::cases(), true);
    }

    public function matches(Type $other): bool
    {
        if ($other instanceof CombiningType) {
            return $other->isMatchedBy($this);
        }

        if ($other instanceof self) {
            return $other->signature->className() === $this->signature->className();
        }

        return $other instanceof UndefinedObjectType
            || $other instanceof MixedType;
    }

    public function signature(): ClassSignature
    {
        return $this->signature;
    }

    public function __toString(): string
    {
        return $this->signature->toString();
    }
}
