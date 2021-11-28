<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fake\Cache\Compiled;

use CuyZ\Valinor\Cache\Compiled\CacheValidationCompiler;

use function is_string;
use function var_export;

final class FakeCacheValidationCompiler implements CacheValidationCompiler
{
    public bool $compileValidation = true;

    public function compile($value): string
    {
        assert(is_string($value));

        return "'$value'";
    }

    public function compileValidation($value): string
    {
        return var_export($this->compileValidation, true);
    }
}
