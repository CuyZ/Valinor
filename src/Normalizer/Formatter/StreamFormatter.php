<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter;

/** @internal */
interface StreamFormatter
{
    public function format(mixed $value): void;

    /**
     * @return resource
     */
    public function resource(): mixed;
}
