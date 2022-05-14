<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use RuntimeException;

/** @internal */
final class UnableToReadFile extends RuntimeException implements SourceException
{
    public function __construct(string $filename)
    {
        parent::__construct(
            "Unable to read the file `$filename`.",
            1629993117
        );
    }
}
