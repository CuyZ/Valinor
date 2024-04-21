<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

use CuyZ\Valinor\Normalizer\Formatter\Compiler\FormatterCompiler;

interface Formatter
{
    public function format(mixed $value): mixed;

    public function compiler(): FormatterCompiler;
}
