<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Shell;
use LogicException;

/** @internal */
final class CannotMapToPermissiveType extends LogicException
{
    public function __construct(Shell $shell)
    {
        $type = $shell->type->toString();

        parent::__construct(
            "Type `$type` at path `{$shell->path}` is not allowed in strict mode. " .
            "In case `$type` is really needed, the `allowPermissiveTypes` setting can be used.",
        );
    }
}
