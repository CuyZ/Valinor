<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache\Compiled;

use function var_export;

/** @internal */
final class MixedValueCacheCompiler implements CacheCompiler
{
    public function compile(mixed $value): string
    {
        return var_export($value, true);
    }
}
