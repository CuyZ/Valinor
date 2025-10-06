<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Exception;

use LogicException;

/** @internal */
final class FileExtensionNotHandled extends LogicException
{
    public function __construct(string $extension)
    {
        parent::__construct("The file extension `$extension` is not handled.");
    }
}
