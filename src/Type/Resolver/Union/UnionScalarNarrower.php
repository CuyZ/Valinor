<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Resolver\Union;

use CuyZ\Valinor\Type\Resolver\Exception\CannotResolveTypeFromUnion;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\UnionType;

use function count;

/** @internal */
final class UnionScalarNarrower implements UnionNarrower
{
    /**
     * @param mixed $source
     */
    public function narrow(UnionType $unionType, $source): Type
    {
        $accepts = [];
        $canCast = [];

        foreach ($unionType->types() as $subType) {
            if ($subType->accepts($source)) {
                $accepts[] = $subType;
            } elseif ($subType instanceof ScalarType && $subType->canCast($source)) {
                $canCast[] = $subType;
            }
        }

        if (count($accepts) === 1) {
            return $accepts[0];
        }

        if (count($canCast) === 1) {
            return $canCast[0];
        }

        throw new CannotResolveTypeFromUnion($unionType);
    }
}
