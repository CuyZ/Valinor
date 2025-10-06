<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use LogicException;

/** @internal */
final class UnableToReadFile extends LogicException
{
    public function __construct(string $filename)
    {
        parent::__construct("Unable to read the file `$filename`.");
    }
}
