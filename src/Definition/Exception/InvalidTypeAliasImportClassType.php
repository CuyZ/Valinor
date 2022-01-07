<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use CuyZ\Valinor\Definition\ClassSignature;
use CuyZ\Valinor\Type\Type;
use LogicException;

/** @internal */
final class InvalidTypeAliasImportClassType extends LogicException
{
    public function __construct(ClassSignature $signature, Type $type)
    {
        parent::__construct(
            "Importing a type alias can only be done with classes, `$type` was given in class `{$signature->className()}`.",
            1638535608
        );
    }
}
