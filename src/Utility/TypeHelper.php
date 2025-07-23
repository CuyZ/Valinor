<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Type\BooleanType;
use CuyZ\Valinor\Type\CompositeTraversableType;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\FloatType;
use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;

/** @internal */
final class TypeHelper
{
    /**
     * Sorting the types by priority: objects, arrays, scalars, everything else.
     */
    public static function typePriority(Type $type): int
    {
        return match (true) {
            $type instanceof ObjectType => 3,
            $type instanceof CompositeTraversableType => 2,
            $type instanceof ScalarType => 1,
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

    public static function dump(Type $type, bool $surround = true): string
    {
        if ($type instanceof EnumType) {
            $text = $type->readableSignature();
        } elseif ($type instanceof FixedType) {
            return $type->toString();
        } elseif (self::containsObject($type)) {
            $text = '?';
        } else {
            $text = $type->toString();
        }

        return $surround ? "`$text`" : $text;
    }

    public static function dumpArguments(Arguments $arguments): string
    {
        if (count($arguments) === 0) {
            return 'array';
        }

        if (count($arguments) === 1) {
            return self::dump($arguments->at(0)->type());
        }

        $parameters = array_map(
            function (Argument $argument) {
                $name = $argument->name();
                $type = $argument->type();

                $signature = self::dump($type, false);

                return $argument->isRequired() ? "$name: $signature" : "$name?: $signature";
            },
            [...$arguments],
        );

        return '`array{' . implode(', ', $parameters) . '}`';
    }

    public static function containsObject(Type $type): bool
    {
        if ($type instanceof CompositeType) {
            foreach ($type->traverse() as $subType) {
                if (self::containsObject($subType)) {
                    return true;
                }
            }
        }

        return $type instanceof ObjectType;
    }
}
