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
        $message = (string)$fullType === (string)$permissiveType
            ? "Type `$fullType` is too permissive."
            : "Type `$permissiveType` in `$fullType` is too permissive.";

        parent::__construct($message, 1655231817);
    }
}
