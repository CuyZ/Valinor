<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use RuntimeException;

/** @internal */
final class ShellHasNoValue extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'Shell has no value.',
            1655029618
        );
    }
}
