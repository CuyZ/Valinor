<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

use CuyZ\Valinor\Type\Type;
use LogicException;

/** @internal */
final class PermissiveTypeFound extends LogicException
{
    public function __construct(Type $fullType, Type $permissiveType)
    {
        $message = $fullType->toString() === $permissiveType->toString()
            ? "Type `{$fullType->toString()}` is too permissive."
            : "Type `{$permissiveType->toString()}` in `{$fullType->toString()}` is too permissive.";

        parent::__construct($message, 1655231817);
    }
}
