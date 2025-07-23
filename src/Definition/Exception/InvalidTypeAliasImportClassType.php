<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use LogicException;

/** @internal */
final class InvalidTypeAliasImportClassType extends LogicException
{
    public function __construct(ObjectType $classType, Type $type)
    {
        parent::__construct(
            "Importing a type alias can only be done with classes, `{$type->toString()}` was given in class `{$classType->className()}`.",
            1638535608
        );
    }
}
