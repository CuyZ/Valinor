<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache\Exception;

use RuntimeException;

/**
 * @codeCoverageIgnore
 * @infection-ignore-all
 */
final class CacheDirectoryNotFound extends RuntimeException
{
    public function __construct(string $directory)
    {
        parent::__construct(
            "Provided directory `$directory` does not exist.",
            1616445016
        );
    }
}
