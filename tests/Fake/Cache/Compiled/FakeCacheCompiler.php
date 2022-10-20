<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Cache\Compiled;

use CuyZ\Valinor\Cache\Compiled\CacheCompiler;

use function is_string;

final class FakeCacheCompiler implements CacheCompiler
{
    public function compile(mixed $value): string
    {
        assert(is_string($value));

        return "'$value'";
    }
}
