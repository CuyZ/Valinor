<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Cache\Compiled;

/** @internal */
interface CacheValidationCompiler extends CacheCompiler
{
    /**
     * @param mixed $value
     */
    public function compileValidation($value): string;
}
