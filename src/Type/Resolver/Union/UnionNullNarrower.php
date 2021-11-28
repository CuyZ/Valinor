<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Resolver\Union;

use CuyZ\Valinor\Type\Resolver\Exception\UnionTypeDoesNotAllowNull;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\UnionType;

final class UnionNullNarrower implements UnionNarrower
{
    private UnionNarrower $delegate;

    public function __construct(UnionNarrower $delegate)
    {
        $this->delegate = $delegate;
    }

    public function narrow(UnionType $unionType, $source): Type
    {
        $allowsNull = $this->findNullType($unionType);

        if ($source === null) {
            if (! $allowsNull) {
                throw new UnionTypeDoesNotAllowNull($unionType);
            }

            return NullType::get();
        }

        $subTypes = $unionType->types();

        if ($allowsNull && count($subTypes) === 2) {
            return $subTypes[0] instanceof NullType
                ? $subTypes[1]
                : $subTypes[0];
        }

        return $this->delegate->narrow($unionType, $source);
    }

    private function findNullType(UnionType $type): bool
    {
        foreach ($type->types() as $subType) {
            if ($subType instanceof NullType) {
                return true;
            }
        }

        return false;
    }
}
