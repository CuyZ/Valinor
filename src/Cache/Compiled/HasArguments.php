<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache\Compiled;

interface HasArguments extends CacheCompiler
{
    /**
     * @return array<string, mixed>
     */
    public function arguments(): array;
}
