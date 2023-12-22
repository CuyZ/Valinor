<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

/**
 * @internal
 *
 * @implements FormatterFactory<ArrayFormatter>
 */
final class ArrayFormatterFactory implements FormatterFactory
{
    public function new(): ArrayFormatter
    {
        return new ArrayFormatter();
    }
}
