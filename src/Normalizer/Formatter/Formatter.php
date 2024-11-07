<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

use CuyZ\Valinor\Normalizer\Transformer\Compiler\FormatterCompiler;

interface Formatter
{
    /**
     * @return array<mixed>|scalar|null
     */
    public function format(mixed $value): mixed;

    public function compiler(): FormatterCompiler;
}
