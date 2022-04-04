<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

use CuyZ\Valinor\Type\CombiningType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ShapedArrayType;

/** @internal */
final class TypeHelper
{
    public static function containsObject(Type $type): bool
    {
        if ($type instanceof ObjectType) {
            return true;
        }

        if ($type instanceof CombiningType) {
            foreach ($type->types() as $subType) {
                if (self::containsObject($subType)) {
                    return true;
                }
            }
        }

        if ($type instanceof ShapedArrayType) {
            foreach ($type->elements() as $element) {
                if (self::containsObject($element->type())) {
                    return true;
                }
            }
        }

        return false;
    }
}
