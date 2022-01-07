<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache\Compiled;

/**
 * @internal
 *
 * @template ValueType
 */
interface PhpCacheFile
{
    /**
     * @return ValueType
     */
    public function value();

    public function isValid(): bool;
}
