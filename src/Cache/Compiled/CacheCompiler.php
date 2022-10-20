<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache\Compiled;

/** @internal */
interface CacheCompiler
{
    public function compile(mixed $value): string;
}
