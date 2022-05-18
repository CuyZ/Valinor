<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;

/** @internal */
final class TypeHelper
{
    public static function dump(Type $type, bool $surround = true): string
    {
        $text = self::containsObject($type) ? '?' : (string)$type;

        return $surround ? "`$text`" : $text;
    }

    public static function containsObject(Type $type): bool
    {
        if ($type instanceof ObjectType) {
            return true;
        }

        if ($type instanceof CompositeType) {
            foreach ($type->traverse() as $subType) {
                if (self::containsObject($subType)) {
                    return true;
                }
            }
        }

        return false;
    }
}
