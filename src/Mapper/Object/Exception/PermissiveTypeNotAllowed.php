<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Type\Type;
use LogicException;

/** @internal */
final class PermissiveTypeNotAllowed extends LogicException
{
    public function __construct(string $argumentSignature, Type $permissiveType)
    {
        parent::__construct(
            "The type of `$argumentSignature` contains `{$permissiveType->toString()}`, which is not " .
            "allowed in strict mode. If really needed, the `allowPermissiveTypes` setting can be used.",
        );
    }
}
