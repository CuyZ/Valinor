<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

/** @internal */
interface StreamFormatter extends Formatter
{
    /**
     * @return resource
     */
    public function format(mixed $value): mixed;

    /**
     * @return resource
     */
    public function resource(): mixed;
}
