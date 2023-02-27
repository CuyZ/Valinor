<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Type\CompositeType;
use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;

/** @internal */
final class TypeHelper
{
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
            [...$arguments]
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

    public static function checkPermissiveType(Type $type): void
    {
        if ($permissiveType = self::findPermissiveType($type)) {
            throw new PermissiveTypeFound($type, $permissiveType);
        }
    }

    private static function findPermissiveType(Type $type): ?Type
    {
        if ($type instanceof CompositeType) {
            foreach ($type->traverse() as $subType) {
                $permissiveType = self::findPermissiveType($subType);

                if ($permissiveType) {
                    return $permissiveType;
                }
            }
        }

        if ($type instanceof MixedType || $type instanceof UndefinedObjectType) {
            return $type;
        }

        return null;
    }
}
