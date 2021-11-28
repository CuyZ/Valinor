<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use RuntimeException;

final class CannotGetParentOfRootShell extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'Impossible to get the parent of a root shell.',
            1630674894
        );
    }
}
