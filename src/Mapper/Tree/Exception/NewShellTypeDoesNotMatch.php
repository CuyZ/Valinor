<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class NewShellTypeDoesNotMatch extends RuntimeException
{
    public function __construct(Shell $shell, Type $newType)
    {
        parent::__construct(
            "Trying to change the type of the shell at path `{$shell->path()}`: `{$newType->toString()}` is not a valid " .
            "subtype of `{$shell->type()->toString()}`.",
            1628845224
        );
    }
}
