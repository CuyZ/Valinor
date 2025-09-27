<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

use CuyZ\Valinor\Type\BooleanType;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\FloatType;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\NullType;

/** @internal */
final class TypeHelper
{
    /**
     * Sorting the types by priority: objects, arrays, scalars/null, everything else.
     */
    public static function typePriority(Type $type): int
    {
        return match (true) {
            $type instanceof ObjectType => 3,
            $type instanceof CompositeTraversableType => 2,
            $type instanceof ScalarType,
            $type instanceof NullType => 1,
            default => 0,
        };
    }

    /**
     * Sorting the scalar types by priority: int, float, string, bool.
     */
    public static function scalarTypePriority(ScalarType $type): int
    {
        return match (true) {
            $type instanceof IntegerType => 4,
            $type instanceof FloatType => 3,
            $type instanceof StringType => 2,
            $type instanceof BooleanType => 1,
            default => 0,
        };
    }

    /**
     * @return list<Type>
     */
    public static function traverseRecursively(Type $type): array
    {
        $types = [];

        if ($type instanceof CompositeType) {
            foreach ($type->traverse() as $subType) {
                $types = [...$types, $subType, ...self::traverseRecursively($subType)];
            }
        }

        return $types;
    }
}
