<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache\Exception;

use RuntimeException;

/**
 * @codeCoverageIgnore
 * @infection-ignore-all
 */
final class CompiledPhpCacheFileNotWritten extends RuntimeException
{
    public function __construct(string $file)
    {
        parent::__construct(
            "File `$file` could not be written.",
            1616445695
        );
    }
}
