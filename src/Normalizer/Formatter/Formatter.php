<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

use CuyZ\Valinor\Normalizer\Transformer\Compiler\FormatterCompiler;

/**
 * @internal
 *
 * @template-covariant T
 */
interface Formatter
{
    /**
     * @return T
     */
    public function format(mixed $value): mixed;

    public function compiler(): FormatterCompiler;
}
