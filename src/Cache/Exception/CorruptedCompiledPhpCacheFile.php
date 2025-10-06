<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache\Exception;

use RuntimeException;

/** @internal */
final class CorruptedCompiledPhpCacheFile extends RuntimeException
{
    public function __construct(string $filename)
    {
        parent::__construct("Compiled php cache file `$filename` has corrupted value.");
    }
}
