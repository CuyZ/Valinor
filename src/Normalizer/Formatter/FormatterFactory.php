<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

/**
 * @internal
 *
 * @template-covariant T of Formatter
 */
interface FormatterFactory
{
    /**
     * @return T
     */
    public function new(): Formatter;
}
