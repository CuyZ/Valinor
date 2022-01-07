<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use RuntimeException;

/** @internal */
final class FileExtensionNotHandled extends RuntimeException implements SourceException
{
    public function __construct(string $extension)
    {
        parent::__construct(
            "The file extension `$extension` is not handled.",
            1629991744
        );
    }
}
